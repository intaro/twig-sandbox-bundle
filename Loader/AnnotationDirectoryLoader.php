<?php

namespace Intaro\TwigSandboxBundle\Loader;

use Intaro\TwigSandboxBundle\SecurityPolicy\SecurityPolicyRules;
use Symfony\Component\Config\Resource\DirectoryResource;

class AnnotationDirectoryLoader extends AnnotationFileLoader
{
    /**
     * @param string $resource A directory path
     */
    public function load(mixed $resource, ?string $type = null): SecurityPolicyRules
    {
        $dir = $this->locator->locate($resource);

        $rules = new SecurityPolicyRules();
        $rules->addResource(new DirectoryResource($dir, '/\.php$/'));
        $files = iterator_to_array(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir), \RecursiveIteratorIterator::LEAVES_ONLY));
        usort(
            $files,
            static fn (\SplFileInfo $a, \SplFileInfo $b) => (string) $a > (string) $b ? 1 : -1
        );

        foreach ($files as $file) {
            if (!$file->isFile() || !str_ends_with($file->getFilename(), '.php')) {
                continue;
            }

            if ($class = $this->findClass($file)) {
                $r = new \ReflectionClass($class);
                if ($r->isAbstract()) {
                    continue;
                }

                $rules->merge($this->loader->load($class, $type));
            }
        }

        return $rules;
    }

    public function supports(mixed $resource, ?string $type = null): bool
    {
        try {
            $path = $this->locator->locate($resource);
        } catch (\Exception) {
            return false;
        }

        return
            is_string($resource)
            && is_dir($path)
            && (!$type || in_array($type, ['annotation', 'attribute'], true));
    }
}
