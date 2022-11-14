<?php

namespace Intaro\TwigSandboxBundle\Annotation;

/**
 * @Annotation
 * @Target({"METHOD", "PROPERTY"})
 */
class Sandbox
{
    /**
     * Type of returned value of target to which annotation applied
     *
     * List of allowed types is defined in `intaro.twig_sandbox.sandbox_annotation.value_types` parameter
     */
    public $type = 'string';

    /**
     * Fully qualified class name of object that property stores or method returns
     */
    public $target;
}
