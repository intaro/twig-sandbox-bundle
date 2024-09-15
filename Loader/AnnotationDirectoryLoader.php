<?php

namespace Intaro\TwigSandboxBundle\Loader;

use Intaro\TwigSandboxBundle\SecurityPolicy\SecurityPolicyRules;
use Symfony\Component\Config\Resource\DirectoryResource;

class AnnotationDirectoryLoader extends AnnotationFileLoader
{
    /**
     * Loads from annotations from a directory.
     *
     * @param string  $path A directory path
     * @param ?string $type The resource type
     *
     * @return SecurityPolicyRules A Rules instance
     *
     * @throws \InvalidArgumentException When annotations can't be parsed
     */
    public function load($path, $type = null): SecurityPolicyRules
    {
        $dir = $this->locator->locate($path);

        $rules = new SecurityPolicyRules();
        $rules->addResource(new DirectoryResource($dir, '/\.php$/'));
        $files = iterator_to_array(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir), \RecursiveIteratorIterator::LEAVES_ONLY));
        usort($files, function (\SplFileInfo $a, \SplFileInfo $b) {
            return (string) $a > (string) $b ? 1 : -1;
        });

        foreach ($files as $file) {
            if (!$file->isFile() || '.php' !== substr($file->getFilename(), -4)) {
                continue;
            }

            if ($class = $this->findClass($file)) {
                $refl = new \ReflectionClass($class);
                if ($refl->isAbstract()) {
                    continue;
                }

                $rules->merge($this->loader->load($class, $type));
            }
        }

        return $rules;
    }

    /**
     * @param ?string $type
     */
    public function supports($resource, $type = null): bool
    {
        try {
            $path = $this->locator->locate($resource);
        } catch (\Exception $e) {
            return false;
        }

        return is_string($resource) && is_dir($path) && (!$type || 'annotation' === $type);
    }
}
