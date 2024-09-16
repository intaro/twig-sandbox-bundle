<?php

namespace Intaro\TwigSandboxBundle\SecurityPolicy;

use Symfony\Component\Config\Resource\ResourceInterface;

/**
 * Contains allowed properties and methods of classes in Twig Sandbox
 */
class SecurityPolicyRules
{
    /**
     * @param array<string, string[]> $methods
     * @param array<string, string[]> $properties
     * @param ResourceInterface[]     $resources
     */
    public function __construct(
        private array $methods = [],
        private array $properties = [],
        private array $resources = [],
    ) {
    }

    /**
     * @return array<string, string[]>
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    /**
     * @return array<string, string[]>
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    public function addMethod(string $class, string $method): void
    {
        if (!isset($this->methods[$class])) {
            $this->methods[$class] = [];
        }

        if (!in_array($method, $this->methods[$class])) {
            $this->methods[$class][] = $method;
        }
    }

    public function addProperty(string $class, string $property): void
    {
        if (!isset($this->properties[$class])) {
            $this->properties[$class] = [];
        }

        if (!in_array($property, $this->properties[$class])) {
            $this->properties[$class][] = $property;
        }
    }

    /**
     * Returns an array of resources loaded to build this rules.
     *
     * @return ResourceInterface[] An array of resources
     */
    public function getResources(): array
    {
        return array_unique($this->resources);
    }

    public function addResource(ResourceInterface $resource): void
    {
        $this->resources[] = $resource;
    }

    public function merge(self $rules): void
    {
        $this->resources = array_merge($this->resources, $rules->getResources());

        foreach ($rules->getMethods() as $class => $methods) {
            foreach ($methods as $method) {
                $this->addMethod($class, $method);
            }
        }

        foreach ($rules->getProperties() as $class => $properties) {
            foreach ($properties as $property) {
                $this->addProperty($class, $property);
            }
        }
    }
}
