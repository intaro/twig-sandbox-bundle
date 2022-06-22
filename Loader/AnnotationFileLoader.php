<?php

namespace Intaro\TwigSandboxBundle\Loader;

use Intaro\TwigSandboxBundle\SecurityPolicy\SecurityPolicyRules;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Config\Resource\FileResource;

class AnnotationFileLoader extends Loader
{
    protected $loader;

    public function __construct(AnnotationClassLoader $loader, string $env = null)
    {
        if (!function_exists('token_get_all')) {
            throw new \RuntimeException('The Tokenizer extension is required for the routing annotation loaders.');
        }

        $this->loader = $loader;

        parent::__construct($env);
    }

    /**
     * Loads from annotations from a file.
     *
     * @param string $path A PHP file path
     * @param string $type The resource type
     *
     * @return SecurityPolicyRules A Rules instance
     *
     * @throws \InvalidArgumentException When annotations can't be parsed
     */
    public function load($path, string $type = null)
    {
        $rules = new SecurityPolicyRules();
        if ($class = $this->findClass($path)) {
            $rules->addResource(new FileResource($path));
            $rules->merge($this->loader->load($class, $type));
        }

        return $rules;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($resource, $type = null): bool
    {
        return is_string($resource) && 'php' === pathinfo($resource, PATHINFO_EXTENSION) && (!$type || 'annotation' === $type);
    }

    /**
     * Returns the full class name for the first class in the file.
     *
     * @param string $file A PHP file path
     *
     * @return string|false Full class name if found, false otherwise
     */
    protected function findClass(string $file)
    {
        $class = false;
        $namespace = false;
        $tokens = token_get_all(file_get_contents($file));
        for ($i = 0, $count = count($tokens); $i < $count; ++$i) {
            $token = $tokens[$i];

            if (!is_array($token)) {
                continue;
            }

            if (true === $class && T_STRING === $token[0]) {
                return $namespace . '\\' . $token[1];
            }

            if (true === $namespace && T_STRING === $token[0]) {
                $namespace = '';
                do {
                    $namespace .= $token[1];
                    $token = $tokens[++$i];
                } while ($i < $count && is_array($token) && in_array($token[0], [T_NS_SEPARATOR, T_STRING]));
            }

            if (T_CLASS === $token[0]) {
                // Entity::class bug
                if ($i > 0 && T_PAAMAYIM_NEKUDOTAYIM === $tokens[$i - 1][0]) {
                    continue;
                }
                $class = true;
            }

            if (T_NAMESPACE === $token[0]) {
                $namespace = true;
            }
        }

        return false;
    }
}
