<?php

namespace Intaro\TwigSandboxBundle\Loader;

use Intaro\TwigSandboxBundle\SecurityPolicy\SecurityPolicyRules;
use Symfony\Component\Config\Resource\DirectoryResource;

class AnnotationDirectoryLoader extends AnnotationFileLoader
{
    /**
     * @param $dir
     * @param $type
     *
     * @return SecurityPolicyRules
     *
     * @throws \ReflectionException
     */
    public function load($dir, $type = null)
    {
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
     * {@inheritdoc}
     */
    public function supports($resource, $type = null): bool
    {
        return is_string($resource) && is_dir($resource) && (!$type || 'annotation' === $type);
    }
}
