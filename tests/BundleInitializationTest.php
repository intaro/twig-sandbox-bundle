<?php

namespace Intaro\TwigSandboxBundle\Tests;

use Intaro\TwigSandboxBundle\Builder\EnvironmentBuilder;
use Intaro\TwigSandboxBundle\IntaroTwigSandboxBundle;
use Intaro\TwigSandboxBundle\Tests\fixtures\Entity\Product;
use Intaro\TwigSandboxBundle\Tests\fixtures\FixtureBundle;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Nyholm\BundleTest\TestKernel;
use Symfony\Component\HttpKernel\KernelInterface;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedMethodError;

class BundleInitializationTest extends KernelTestCase
{
    protected static function getKernelClass(): string
    {
        return TestKernel::class;
    }

    protected static function createKernel(array $options = []): KernelInterface
    {
        $kernel = parent::createKernel($options);
        $kernel->addTestBundle(IntaroTwigSandboxBundle::class);
        $kernel->addTestBundle(FixtureBundle::class);
        $kernel->handleOptions($options);

        return $kernel;
    }

    public function testInitBundle(): void
    {
        self::bootKernel();
        $container = self::$container;

        $this->assertTrue($container->has('intaro.twig_sandbox.builder'));
        $this->assertTrue($container->has(\Intaro\TwigSandboxBundle\Builder\EnvironmentBuilder::class));
        $service = $container->get('intaro.twig_sandbox.builder');
        $this->assertInstanceOf(EnvironmentBuilder::class, $service);
    }

    public function testRender()
    {
        self::bootKernel();
        $container = self::$container;

        /** @var EnvironmentBuilder $twig */
        $twig = $container->get('intaro.twig_sandbox.builder');
        $html = $twig
            ->getSandboxEnvironment()
            ->render('Product {{ product.name }}', [
                'product' => $this->getObject(),
            ]);

        $this->assertEquals('Product Product 1', $html);
    }

    public function testRenderWithFilter()
    {
        self::bootKernel();
        $container = self::$container;

        /** @var EnvironmentBuilder $twig */
        $twig = $container->get('intaro.twig_sandbox.builder');
        $html = $twig
            ->getSandboxEnvironment()
            ->render('Product {{ product.name|lower }}', [
                'product' => $this->getObject(),
            ]);

        $this->assertEquals('Product product 1', $html);
    }

    public function testRenderError()
    {
        $this->expectException(SecurityNotAllowedMethodError::class);
        $this->expectExceptionMessageMatches('/Calling "getquantity" method on a ".*Product" object is not allowed in/');

        self::bootKernel();
        $container = self::$container;

        /** @var EnvironmentBuilder $twig */
        $twig = $container->get('intaro.twig_sandbox.builder');
        $twig
            ->getSandboxEnvironment()
            ->render('Product {{ product.quantity }}', [
                'product' => $this->getObject(),
            ]);
    }

    public function testRenderWithEmptyConfig(): void
    {
        $this->expectException(SecurityNotAllowedFilterError::class);
        $this->expectExceptionMessageMatches('/Filter "lower" is not allowed in/');

        $kernel = self::bootKernel(['config' => static function (TestKernel $kernel) {
            $kernel->addTestBundle(IntaroTwigSandboxBundle::class);
            $kernel->addTestBundle(FixtureBundle::class);

            $kernel->addTestConfig(__DIR__ . '/fixtures/empty-config.yml');
        }]);

        $container = self::$container;
        /** @var EnvironmentBuilder $twig */
        $twig = $container->get('intaro.twig_sandbox.builder');
        $html = $twig
            ->getSandboxEnvironment()
            ->render('Product {{ product.name|lower }}', [
                'product' => $this->getObject(),
            ]);

        $this->assertEquals('Product product 1', $html);
    }

    private function getObject(): Product
    {
        $product = new Product();
        $product->setName('Product 1');
        $product->setQuantity(5);

        return $product;
    }

}
