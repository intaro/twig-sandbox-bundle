<?php

namespace Intaro\TwigSandboxBundle\Tests;

use Intaro\TwigSandboxBundle\Builder\EnvironmentBuilder;
use Intaro\TwigSandboxBundle\IntaroTwigSandboxBundle;
use Intaro\TwigSandboxBundle\Tests\fixtures\Entity\Product;
use Intaro\TwigSandboxBundle\Tests\fixtures\FixtureBundle;
use Intaro\TwigSandboxBundle\Validator\Constraints\TwigSandbox;
use Nyholm\BundleTest\TestKernel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
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
        /** @var TestKernel $kernel */
        $kernel = parent::createKernel($options);
        if ($kernel::MAJOR_VERSION >= 6) {
            $kernel->addTestConfig(__DIR__ . '/config/framework.yaml');
        }
        $kernel->addTestBundle(IntaroTwigSandboxBundle::class);
        $kernel->addTestBundle(FixtureBundle::class);
        $kernel->handleOptions($options);

        return $kernel;
    }

    public function testInitBundle(): void
    {
        self::bootKernel();
        $container = property_exists(__CLASS__, 'container') ? self::$container : self::getContainer();

        $this->assertTrue($container->has(EnvironmentBuilder::class));
        $this->assertTrue($container->has(EnvironmentBuilder::class));
        $service = $container->get(EnvironmentBuilder::class);
        $this->assertInstanceOf(EnvironmentBuilder::class, $service);
    }

    public function testRender(): void
    {
        self::bootKernel();
        $container = property_exists(__CLASS__, 'container') ? self::$container : self::getContainer();

        $twig = $container->get(EnvironmentBuilder::class)->getSandboxEnvironment();
        $tpl = $twig->createTemplate('Product {{ product.name }}');

        $html = $tpl->render([
            'product' => $this->getObject(),
        ]);

        $this->assertEquals('Product Product 1', $html);
    }

    public function testRenderWithFilter(): void
    {
        self::bootKernel();
        $container = property_exists(__CLASS__, 'container') ? self::$container : self::getContainer();

        $twig = $container->get(EnvironmentBuilder::class)->getSandboxEnvironment();
        $tpl = $twig->createTemplate('Product {{ product.name|lower }}');

        $html = $tpl->render([
            'product' => $this->getObject(),
        ]);

        $this->assertEquals('Product product 1', $html);
    }

    public function testRenderError(): void
    {
        $this->expectException(SecurityNotAllowedMethodError::class);
        $this->expectExceptionMessageMatches('/Calling "getquantity" method on a ".*Product" object is not allowed in/');

        self::bootKernel();
        $container = property_exists(__CLASS__, 'container') ? self::$container : self::getContainer();

        $twig = $container->get(EnvironmentBuilder::class)->getSandboxEnvironment();
        $tpl = $twig->createTemplate('Product {{ product.quantity }}');

        $tpl->render([
            'product' => $this->getObject(),
        ]);
    }

    public function testRenderWithEmptyConfig(): void
    {
        $this->expectException(SecurityNotAllowedFilterError::class);
        $this->expectExceptionMessageMatches('/Filter "lower" is not allowed in/');

        $kernel = self::bootKernel(['config' => static function (TestKernel $kernel): void {
            $kernel->addTestBundle(IntaroTwigSandboxBundle::class);
            $kernel->addTestBundle(FixtureBundle::class);

            $kernel->addTestConfig(__DIR__ . '/fixtures/empty-config.yml');
        }]);

        $container = property_exists(__CLASS__, 'container') ? self::$container : self::getContainer();
        $twig = $container->get(EnvironmentBuilder::class)->getSandboxEnvironment();
        $tpl = $twig->createTemplate('Product {{ product.name|lower }}');

        $html = $tpl->render([
            'product' => $this->getObject(),
        ]);

        $this->assertEquals('Product product 1', $html);
    }

    public function testValidationIsValid(): void
    {
        self::bootKernel();
        $container = property_exists(__CLASS__, 'container') ? self::$container : self::getContainer();

        /** @var ValidatorInterface $validator */
        $validator = $container->get(ValidatorInterface::class);
        $this->assertCount(0, $validator->validate('', new TwigSandbox()));
        $this->assertCount(0, $validator->validate('ddd', new TwigSandbox()));
        $this->assertCount(0, $validator->validate('{{ v }}', new TwigSandbox()));
        $this->assertCount(0, $validator->validate('{{ v.name }}', new TwigSandbox([
            'strict' => true,
            'vars' => ['v' => Product::class],
        ])));
    }

    public function testValidationIsNotValid(): void
    {
        self::bootKernel();
        $container = property_exists(__CLASS__, 'container') ? self::$container : self::getContainer();

        /** @var ValidatorInterface $validator */
        $validator = $container->get(ValidatorInterface::class);

        $result = $validator->validate(
            '{{ v }}',
            new TwigSandbox(['strict' => true])
        );
        $this->assertCount(1, $result);
        $this->assertStringContainsString(
            'Variable "v" does not exist',
            $result->get(0)->getParameters()['{{ syntax_error }}']
        );

        $result = $validator->validate(
            '{{ p.name }}{{ v }}',
            new TwigSandbox([
                'strict' => true,
                'vars' => ['p' => Product::class],
            ])
        );
        $this->assertCount(1, $result);
        $this->assertStringContainsString(
            'Variable "v" does not exist',
            $result->get(0)->getParameters()['{{ syntax_error }}']
        );

        $result = $validator->validate(
            '{{ ddd',
            new TwigSandbox()
        );
        $this->assertCount(1, $result);
        $this->assertStringContainsString(
            'Unexpected token "end of template"',
            $result->get(0)->getParameters()['{{ syntax_error }}']
        );
    }

    private function getObject(): Product
    {
        $product = new Product();
        $product->setName('Product 1');
        $product->setQuantity(5);

        return $product;
    }
}
