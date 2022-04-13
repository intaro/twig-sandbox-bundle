<?php

namespace Intaro\TwigSandboxBundle\Builder;

use Twig\Environment;

class TwigAdapter
{

    private Environment $twigEnvironment;

    public function __construct(Environment $twigEnvironment)
    {
        $this->twigEnvironment = $twigEnvironment;
    }

    public function __call(string $method, array $args)
    {
        if (method_exists($this, $method)) {
            return $this->{$method(...$args)};
        }

        return $this->twigEnvironment->{$method}(...$args);
    }

    public function render($template, array $context = []): string
    {
        $template = $this->twigEnvironment->createTemplate($template);

        return $template->render($context);
    }

    public function getTwig(): Environment
    {
        return $this->twigEnvironment;
    }
}
