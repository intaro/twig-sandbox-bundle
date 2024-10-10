<?php

namespace Intaro\TwigSandboxBundle\CacheWarmer;

use Intaro\TwigSandboxBundle\Builder\EnvironmentBuilder;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

class TwigSandboxCacheWarmer implements CacheWarmerInterface
{
    protected EnvironmentBuilder $environmentBuilder;

    public function __construct(EnvironmentBuilder $builder)
    {
        $this->environmentBuilder = $builder;
    }

    public function warmUp(string $cacheDir, ?string $buildDir = null): array
    {
        return $this->environmentBuilder->warmUp($cacheDir);
    }

    public function isOptional(): bool
    {
        return true;
    }
}
