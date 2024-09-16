<?php

namespace Intaro\TwigSandboxBundle\Loader;

use Intaro\TwigSandboxBundle\Attribute\Sandbox;
use Intaro\TwigSandboxBundle\SecurityPolicy\SecurityPolicyRules;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\Config\Resource\FileResource;

class AnnotationClassLoader implements LoaderInterface
{
    /**
     * @param string $resource A class name
     */
    public function load(mixed $resource, ?string $type = null): SecurityPolicyRules
    {
        if (!class_exists($resource)) {
            throw new \InvalidArgumentException(sprintf('Class "%s" does not exist.', $resource));
        }

        $class = new \ReflectionClass($resource);
        if ($class->isAbstract()) {
            throw new \InvalidArgumentException(sprintf('Attributes from the class "%s" cannot be read as it is abstract.', $resource));
        }

        $rules = new SecurityPolicyRules();
        $rules->addResource(new FileResource((string) $class->getFileName()));

        foreach ($class->getMethods() as $method) {
            if (!count($method->getAttributes(Sandbox::class, \ReflectionAttribute::IS_INSTANCEOF))) {
                continue;
            }

            $rules->addMethod($class->getName(), $method->getName());
        }

        foreach ($class->getProperties() as $property) {
            if (!count($property->getAttributes(Sandbox::class, \ReflectionAttribute::IS_INSTANCEOF))) {
                continue;
            }

            $rules->addProperty($class->getName(), $property->getName());
        }

        return $rules;
    }

    public function supports(mixed $resource, ?string $type = null): bool
    {
        return
            is_string($resource)
            && preg_match('/^(?:\\\\?[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)+$/', $resource)
            && (!$type || in_array($type, ['annotation', 'attribute'], true));
    }

    public function setResolver(LoaderResolverInterface $resolver): void
    {
    }

    /**
     * @return LoaderResolverInterface|null
     */
    public function getResolver()
    {
        return null;
    }
}
