<?php

namespace Intaro\TwigSandboxBundle\Tests;

use Intaro\TwigSandboxBundle\Builder\EnvironmentBuilder;
use Intaro\TwigSandboxBundle\Dumper\PhpDumper;
use Intaro\TwigSandboxBundle\Loader\AnnotationClassLoader;
use Intaro\TwigSandboxBundle\Loader\AnnotationDirectoryLoader;
use Intaro\TwigSandboxBundle\Loader\AnnotationFileLoader;
use Intaro\TwigSandboxBundle\Tests\fixtures\Entity\Product;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;
use Twig\Sandbox\SecurityPolicy;

class SimpleTest extends TestCase
{
    public function testRender(): void
    {
        $twigAdapter = $this->createTwigEnv();

        $product = new Product();
        $product->setName('Product 1');
        $product->setQuantity(5);

        $twig = $twigAdapter->getTwig();
        $tpl = $twig->createTemplate('Product {{ product.name }}');
        $html = $tpl->render([
            'product' => $product,
        ]);

        $this->assertEquals('Product Product 1', $html);

        $html = $twigAdapter->render('Product {{ product.name }}', [
            'product' => $product,
        ]);

        $this->assertEquals('Product Product 1', $html);
    }

    private function createTwigEnv()
    {
        $annotationClassLoader = new AnnotationClassLoader();
        $annotationDirectoryLoader = new AnnotationDirectoryLoader(new FileLocator(), $annotationClassLoader);
        $annotationFileLoader = new AnnotationFileLoader(new FileLocator(), $annotationClassLoader);

        $loaderResolver = new LoaderResolver([
            $annotationDirectoryLoader,
            $annotationFileLoader,
            $annotationClassLoader,
        ]);

        $loader = new DelegatingLoader($loaderResolver);
        $securityPolicy = new SecurityPolicy([], ['escape']);

        $builder = new EnvironmentBuilder(
            $loader,
            new PhpDumper(),
            $securityPolicy,
            [
                'cache_dir' => __DIR__,
                'cache_filename' => 'IntaroTwigSandboxPolicy',
                'bundles' => ['Intaro\TwigSandboxBundle\Tests\fixtures\FixtureBundle'], // %kernel.bundles%
                'debug' => false, // %kernel.debug%
                'additional_paths' => [],
            ]
        );

        return $builder->getSandboxEnvironment();
    }
}
