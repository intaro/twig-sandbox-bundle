<?php

namespace Intaro\TwigSandboxBundle\Builder;

use Intaro\TwigSandboxBundle\Dumper\DumperInterface;
use Intaro\TwigSandboxBundle\SecurityPolicy\SecurityPolicyRules;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\Extension\SandboxExtension;
use Twig\Loader\ArrayLoader;
use Twig\Sandbox\SecurityPolicy;

/**
 * Builder of twig sandbox environment with specified rights
 */
class EnvironmentBuilder implements WarmableInterface
{
    private LoaderInterface $loader;
    /** @var array<string, mixed> */
    private array $options;
    private ?SecurityPolicy $policy;
    private ?SecurityPolicyRules $rules = null;
    /** @var AbstractExtension[] */
    private array $extensions = [];
    private DumperInterface $dumper;

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(LoaderInterface $loader, DumperInterface $dumper, ?SecurityPolicy $policy = null, array $options = [])
    {
        $this->loader = $loader;
        $this->policy = $policy;
        $this->setOptions($options);
        $this->dumper = $dumper;
    }

    /**
     * Additional extension for sandbox environment
     */
    public function addExtension(AbstractExtension $extension): void
    {
        $this->extensions[] = $extension;
    }

    /**
     * @param AbstractExtension[]|null $extensions
     */
    public function addExtensions(?array $extensions = null): void
    {
        if (!is_array($extensions)) {
            return;
        }

        foreach ($extensions as $extension) {
            $this->addExtension($extension);
        }
    }

    /**
     * Формирует окружение для Twig Sandbox
     *
     * @param array<string, mixed> $params
     */
    public function getSandboxEnvironment(array $params = [], ?SecurityPolicy $securityPolicy = null): TwigAdapter
    {
        $loader = new ArrayLoader();
        $twig = new Environment($loader, $params);

        if (!$securityPolicy) {
            $this->initSecurityPolicy();
            $sandboxExtension = new SandboxExtension($this->policy, true);
        } else {
            $sandboxExtension = new SandboxExtension($securityPolicy, true);
        }
        $twig->addExtension($sandboxExtension);

        foreach ($this->extensions as $extension) {
            $twig->addExtension($extension);
        }

        return new TwigAdapter($twig);
    }

    /**
     * @param array<string, mixed> $options
     */
    public function setOptions(array $options): void
    {
        $this->options = [
            'cache_dir' => null,
            'cache_filename' => 'IntaroTwigSandboxPolicy',
            'bundles' => [],
            'debug' => false,
            'additional_paths' => [],
        ];

        // check option names and live merge, if errors are encountered Exception will be thrown
        $invalid = [];
        foreach ($options as $key => $value) {
            if (array_key_exists($key, $this->options)) {
                $this->options[$key] = $value;
            } else {
                $invalid[] = $key;
            }
        }

        if ($invalid) {
            throw new \InvalidArgumentException(sprintf('The EnvironmentBuilder does not support the following options: "%s".', implode('\', \'', $invalid)));
        }
    }

    /**
     * @phpstan-assert SecurityPolicy $this->policy
     */
    private function initSecurityPolicy(): void
    {
        $rules = $this->getPolicyRules();

        if (!$this->policy) {
            $this->policy = new SecurityPolicy();
        }

        $this->policy->setAllowedProperties($rules->getProperties());
        $this->policy->setAllowedMethods($rules->getMethods());
    }

    public function getPolicyRules(): SecurityPolicyRules
    {
        if ($this->rules) {
            return $this->rules;
        }

        if (null === $this->options['cache_dir'] || null === $this->options['cache_filename']) {
            throw new \RuntimeException('Options "cache_dir" and "cache_filename" must be defined.');
        }

        $cache = new ConfigCache(
            $this->options['cache_dir'] . '/' . $this->options['cache_filename'] . '.php',
            $this->options['debug']
        );

        if (!$cache->isFresh()) {
            $rules = new SecurityPolicyRules();

            foreach ($this->options['bundles'] as $bundle) {
                $refl = new \ReflectionClass($bundle);
                $dir = dirname((string) $refl->getFileName()) . '/Entity';
                if (file_exists($dir) && is_dir($dir)) {
                    $rules->merge($this->loader->load($dir));
                }

                $additionalPaths = $this->options['additional_paths'][$refl->getShortName()] ?? [];
                foreach ($additionalPaths as $path) {
                    $additionalDir = dirname((string) $refl->getFileName()) . '/' . $path;
                    if (file_exists($additionalDir) && is_dir($additionalDir)) {
                        $rules->merge($this->loader->load($additionalDir));
                    }
                }
            }

            $cache->write($this->dumper->dump($rules), $rules->getResources());
        }

        $this->rules = include $cache->getPath();

        return $this->rules;
    }

    public function warmUp(string $cacheDir, ?string $buildDir = null): array
    {
        $currentDir = $this->options['cache_dir'];

        // force cache generation
        $this->options['cache_dir'] = $cacheDir;
        $this->getPolicyRules();

        $this->options['cache_dir'] = $currentDir;

        return [];
    }
}
