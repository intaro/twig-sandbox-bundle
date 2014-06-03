<?php

namespace Intaro\TwigSandboxBundle\Builder;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;
use Intaro\TwigSandboxBundle\SecurityPolicy\SecurityPolicy;
use Intaro\TwigSandboxBundle\SecurityPolicy\SecurityPolicyRules;

/**
 * Builder of twig sandbox environment with specified rights
 */
class EnvironmentBuilder implements WarmableInterface
{
    private $loader;
    private $options;
    private $policy;
    private $rules;
    private $extensions = array();

    public function __construct(LoaderInterface $loader, SecurityPolicy $policy = null, array $options = array())
    {
        $this->loader = $loader;
        $this->policy = $policy;
        $this->setOptions($options);
    }

    /**
     * Additional extension for sandbox environment
     *
     * @access public
     * @param \Twig_Extension $extension
     * @return void
     */
    public function addExtension(\Twig_Extension $extension)
    {
        $this->extensions[] = $extension;
    }

    /**
     * Additional extensions for sandbox environment
     *
     * @access public
     * @param \Twig_Extension $extension
     * @return void
     */
    public function addExtensions($extensions = null)
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
     * @access public
     * @param SecurityPolicy $policy
     * @param mixed $params (default: [])
     * @return \Twig_Environment
     */
    public function getSandboxEnvironment($params = array(), SecurityPolicy $securityPolicy = null)
    {
        $loader = new \Twig_Loader_String();
        $twig = new \Twig_Environment($loader, $params);

        if (!$securiyPolicy) {
            $this->initSecurityPolicy();
            $sandboxExtension = new \Twig_Extension_Sandbox($this->policy, true);
        }
        else {
            $sandboxExtension = new \Twig_Extension_Sandbox($securityPolicy, true);
        }
        $twig->addExtension($sandboxExtension);

        foreach ($this->extensions as $extension) {
            $twig->addExtension($extension);
        }

        return $twig;
    }

    public function setOptions(array $options)
    {
        $this->options = array(
            'cache_dir'      => null,
            'cache_filename' => 'IntaroCRMTwigSandboxPolicy',
            'dumper_class'   => null,
            'bundles'        => array(),
            'debug'          => false,
        );

        // check option names and live merge, if errors are encountered Exception will be thrown
        $invalid = array();
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

    private function initSecurityPolicy()
    {
        $rules = $this->getPolicyRules();

        if (!$this->policy) {
            $this->policy = new SecurityPolicy();
        }

        $this->policy->setAllowedProperties($rules->getProperties());
        $this->policy->setAllowedMethods($rules->getMethods());
    }

    public function getPolicyRules()
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

                $dir = dirname($refl->getFileName()) . '/Entity';
                if (file_exists($dir) && is_dir($dir)) {
                    $rules->merge($this->loader->load($dir));
                }
            }

            $dumper = new $this->options['dumper_class']();
            $cache->write($dumper->dump($rules), $rules->getResources());
        }

        $this->rules = include $cache;

        return $this->rules;
    }

    /**
     * {@inheritdoc}
     */
    public function warmUp($cacheDir)
    {
        $currentDir = $this->options['cache_dir'];

        // force cache generation
        $this->options['cache_dir'] = $cacheDir;
        $this->getPolicyRules();

        $this->options['cache_dir'] = $currentDir;
    }

}