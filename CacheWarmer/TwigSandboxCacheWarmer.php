<?php

namespace Intaro\TwigSandboxBundle\CacheWarmer;

use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;
use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;
use Intaro\TwigSandboxBundle\Builder\EnvironmentBuilder;

class TwigSandboxCacheWarmer implements CacheWarmerInterface
{
    protected $environmentBuilder;

    public function __construct(EnvironmentBuilder $builder)
    {
        $this->environmentBuilder = $builder;
    }

    /**
     * Warms up the cache.
     *
     * @param string $cacheDir The cache directory
     */
    public function warmUp($cacheDir): array
    {
        if ($this->environmentBuilder instanceof WarmableInterface) {
            return $this->environmentBuilder->warmUp($cacheDir);
        }

        return [];
    }

    /**
     * Checks whether this warmer is optional or not.
     *
     * Optional warmers can be ignored on certain conditions.
     *
     * A warmer should return true if the cache can be
     * generated incrementally and on-demand.
     *
     * @return Boolean true if the warmer is optional, false otherwise
     */
    public function isOptional()
    {
        return true;
    }
}
