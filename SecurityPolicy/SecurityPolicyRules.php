<?php

namespace Intaro\TwigSandboxBundle\SecurityPolicy;

use Symfony\Component\Config\Resource\ResourceInterface;

/**
 * Contains allowed properties and methods of classes in Twig Sandbox
 */
class SecurityPolicyRules
{
    private $methods;
    private $properties;
    private $resources;

    public function __construct(array $methods = [], array $properties = [], array $resources = [])
    {
        $this->methods = $methods;
        $this->properties = $properties;
        $this->resources = $resources;
    }

    public function getMethods()
    {
        return $this->methods;
    }

    public function getProperties()
    {
        return $this->properties;
    }

    public function addMethod($class, $method)
    {
        if (!isset($this->methods[$class])) {
            $this->methods[$class] = [];
        }

        if (!in_array($method, $this->methods[$class])) {
            $this->methods[$class][] = $method;
        }
    }

    public function addProperty($class, $property)
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
    public function getResources()
    {
        return array_unique($this->resources);
    }

    /**
     * Adds a resource for this rules.
     *
     * @param ResourceInterface $resource A resource instance
     */
    public function addResource(ResourceInterface $resource)
    {
        $this->resources[] = $resource;
    }

    public function merge(SecurityPolicyRules $rules)
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
