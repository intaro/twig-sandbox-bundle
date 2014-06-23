<?php

namespace Intaro\TwigSandboxBundle\Loader;

use Doctrine\Common\Annotations\Reader;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\Config\Resource\FileResource;
use Intaro\TwigSandboxBundle\SecurityPolicy\SecurityPolicyRules;

class AnnotationClassLoader implements LoaderInterface
{
    protected $reader;
    protected $annotationClass  = 'Intaro\\TwigSandboxBundle\\Annotation\\Sandbox';

    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * Sets the annotation class to read  properties from.
     *
     * @param string $class A fully-qualified class name
     */
    public function setAnnotationClass($class)
    {
        $this->annotationClass = $class;
    }

    /**
     * Loads from annotations from a class.
     *
     * @param string $class A class name
     * @param string $type  The resource type
     *
     * @return SecurityPolicyRules A Rules instance
     *
     * @throws \InvalidArgumentException When annotations can't be parsed
     */
    public function load($class, $type = null)
    {
        if (!class_exists($class)) {
            throw new \InvalidArgumentException(sprintf('Class "%s" does not exist.', $class));
        }

        $class = new \ReflectionClass($class);
        if ($class->isAbstract()) {
            throw new \InvalidArgumentException(sprintf('Annotations from class "%s" cannot be read as it is abstract.', $class));
        }

        $rules = new SecurityPolicyRules();
        $rules->addResource(new FileResource($class->getFileName()));

        foreach ($class->getMethods() as $method) {
            foreach ($this->reader->getMethodAnnotations($method) as $annot) {
                if ($annot instanceof $this->annotationClass) {
                    $methodName = $method->getName();
                    $rules->addMethod($class->getName(), $methodName);
                }
            }
        }

        foreach ($class->getProperties() as $property) {
            foreach ($this->reader->getPropertyAnnotations($property) as $annot) {
                if ($annot instanceof $this->annotationClass) {
                    $rules->addProperty($class->getName(), $property->getName());
                }
            }
        }

        return $rules;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($resource, $type = null)
    {
        return is_string($resource) && preg_match('/^(?:\\\\?[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)+$/', $resource) && (!$type || 'annotation' === $type);
    }

    /**
     * {@inheritdoc}
     */
    public function setResolver(LoaderResolverInterface $resolver)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getResolver()
    {
    }
}