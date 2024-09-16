<?php

namespace Intaro\TwigSandboxBundle\Builder;

use Twig\Environment;

/**
 * @mixin Environment
 */
class TwigAdapter
{
    private Environment $twigEnvironment;

    public function __construct(Environment $twigEnvironment)
    {
        $this->twigEnvironment = $twigEnvironment;
    }

    /**
     * @param mixed[] $args
     */
    public function __call(string $method, array $args): mixed
    {
        if (method_exists($this, $method)) {
            return $this->$method(...$args);
        }

        return $this->twigEnvironment->$method(...$args);
    }

    /**
     * @param array<string, mixed> $context
     */
    public function render(string $template, array $context = []): string
    {
        return $this->twigEnvironment
            ->createTemplate($template)
            ->render($context)
        ;
    }

    public function getTwig(): Environment
    {
        return $this->twigEnvironment;
    }
}
