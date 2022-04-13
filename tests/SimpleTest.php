<?php

namespace Intaro\TwigSandboxBundle\Tests;

use Intaro\TwigSandboxBundle\Builder\EnvironmentBuilder;
use Intaro\TwigSandboxBundle\Loader\AnnotationClassLoader;
use Intaro\TwigSandboxBundle\Loader\AnnotationDirectoryLoader;
use Intaro\TwigSandboxBundle\Loader\AnnotationFileLoader;
use Intaro\TwigSandboxBundle\Tests\fixtures\Entity\Product;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\FileLocator;

class SimpleTest extends TestCase
{

    public function testRender()
    {
        $twigAdapter = $this->createTwigEnv();

        $product = new Product();
        $product->setName('Product 1');
        $product->setQuantity(5);

        $twig = $twigAdapter->getTwig();
        $tpl = $twig->createTemplate('Product {{ product.name }}');
        $html1 = $tpl->render([
            'product' => $product,
        ]);

        $this->assertEquals('Product Product 1', $html1);


        $html1 = $twigAdapter->render('Product {{ product.name }}', [
            'product' => $product,
        ]);

        $this->assertEquals('Product Product 1', $html1);
    }

    private function createTwigEnv()
    {
        $annotationClassLoader = new AnnotationClassLoader(new \Doctrine\Common\Annotations\AnnotationReader());
        $annotationDirectoryLoader = new AnnotationDirectoryLoader(new FileLocator(), $annotationClassLoader);
        $annotationFileLoader = new AnnotationFileLoader(new FileLocator(), $annotationClassLoader);

        $loaderResolver = new \Symfony\Component\Config\Loader\LoaderResolver([
                $annotationDirectoryLoader,
                $annotationFileLoader,
                $annotationClassLoader,
            ]
        );

        $loader = new \Symfony\Component\Config\Loader\DelegatingLoader($loaderResolver);
        $securityPolicy = new \Twig\Sandbox\SecurityPolicy([], ['escape']);

        $builder = new EnvironmentBuilder(
            $loader,
            $securityPolicy,
            [
                'cache_dir' => __DIR__,
                'cache_filename' => 'IntaroTwigSandboxPolicy',
                'dumper_class' => "Intaro\TwigSandboxBundle\Dumper\PhpDumper",
                'bundles' => ['Intaro\TwigSandboxBundle\Tests\fixtures\FixtureBundle'], // %kernel.bundles%
                'debug' => false, // %kernel.debug%
            ]
        );

        return $builder->getSandboxEnvironment();
    }
}