<?php

namespace Intaro\TwigSandboxBundle\Attribute;

#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_PROPERTY)]
class Sandbox
{
    public function __construct(
        /**
         * Type of returned value of target to which annotation applied
         * List of allowed types is defined in `intaro.twig_sandbox.sandbox_annotation.value_types` parameter
         */
        public string $type = 'string'
    ) {
    }
}
