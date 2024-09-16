<?php

namespace Intaro\TwigSandboxBundle\Annotation;

/**
 * @deprecated left only for automatic conversion of annotations to attributes
 *
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
    public string $type = 'string';
}
