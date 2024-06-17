<?php

namespace Intaro\TwigSandboxBundle\CacheWarmer;

use Intaro\TwigSandboxBundle\Builder\EnvironmentBuilder;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;
use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;

class TwigSandboxCacheWarmer implements CacheWarmerInterface
{
    protected $environmentBuilder;

    public function __construct(EnvironmentBuilder $builder)
    {
        $this->environmentBuilder = $builder;
    }

    public function warmUp(/*string */ $cacheDir/*, ?string $buildDir = null*/)/*: array*/
    {
        if ($this->environmentBuilder instanceof WarmableInterface) {
            return $this->environmentBuilder->warmUp($cacheDir);
        }

        return [];
    }

    public function isOptional(): bool
    {
        return true;
    }
}
