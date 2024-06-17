# TwigSandboxBundle

![CI](https://github.com/intaro/twig-sandbox-bundle/workflows/CI/badge.svg?branch=master)

There is [Twig](https://twig.symfony.com)-extension [Sandbox](https://twig.symfony.com/doc/2.x/api.html#sandbox-extension) which can be used to evaluate untrusted code and where access to unsafe attributes and methods is prohibited. This bundle allows to configure security policy for sandbox.

## Installation

TwigSandboxBundle requires Symfony 4.4 or higher.

Require the bundle in your `composer.json` file:

```json
{
    "require": {
        "intaro/twig-sandbox-bundle": "^2.0"
    }
}
```

Register the bundle in `AppKernel`:

```php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = [
        //...

        new Intaro\TwigSandboxBundle\IntaroTwigSandboxBundle(),
    ];

    //...
}
```

Install the bundle:

```
$ composer require intaro/twig-sandbox-bundle
```

## Usage

Define allowed properties and methods for your entities using annotation `@Sandbox`.
Optionally you can add `type` option for annotation.
This option defines type of value that property stores or method returns.

In your application you can use annotation reader to extract value of `type` option and use this value
to perform additional checks or any other actions, for example, use twig filters according to value of the option.

```php
<?php
// Acme/DemoBundle/Entity/Product.php

namespace Acme\DemoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Intaro\TwigSandboxBundle\Annotation\Sandbox;

/**
 * @ORM\Table()
 * @ORM\Entity
 */
class Product
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * @var string $name
     *
     * @Sandbox(type="string")
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var integer $quantity
     *
     * @ORM\Column(name="quantity", type="integer", nullable=true)
     */
    private $quantity;


    /**
     * Get id
     *
     * @Sandbox(type="int")
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Set name
     *
     * @param string $name
     * @return Product
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }
    
    /**
     * Get name
     *
     * @Sandbox
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set quantity
     *
     * @param boolean $quantity
     * @return Product
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }
    
    /**
     * Get quantity
     *
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

}

```

And use sandbox environment.

```php

use Acme\DemoBundle\Entity\Product;
use Intaro\TwigSandboxBundle\Builder\EnvironmentBuilder;

class Example {

    private EnvironmentBuilder $environmentBuilder;
    
    public function __construct(EnvironmentBuilder $environmentBuilder)
    {
        $this->environmentBuilder = $environmentBuilder;
    }
    
    $twig = $this->environmentBuilder->getSandboxEnvironment();
    
    $product = new Product();
    $product->setName('Product 1');
    $product->setQuantity(5);
    
    //success render
    $html1 = $twig->render(
        'Product {{ product.name }}',
        [
            'product' => $product,
        ]
    );
    
    //render with exception
    $html2 = $twig->render(
        'Product {{ product.name }} in the quantity {{ product.quantity }}',
        [
            'product' => $product,
        ]
    );
    
}
```

### Validation

You can validate entity fields which contain twig templates with TwigSandbox validator.

```php
//in Entity/Page.php

use Intaro\TwigSandboxBundle\Validator\Constraints\TwigSandbox;

class Page
{
    //...
    
    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('template', new TwigSandbox());
    }
    
    //...
}

```

## Configure

### Methods and properties

You can define allowed methods and properties of entities with annotation `Intaro\TwigSandboxBundle\Annotation\Sandbox`. Example above.

### Tags 

Default list of the allowed tags:
```yml
- 'autoescape'
- 'filter'
- 'do'
- 'flush'
- 'for'
- 'set'
- 'verbatium'
- 'if'
- 'spaceless'
```

You can override list in the parameter `intaro.twig_sandbox.policy_tags`:
```yml
# app/config/config.yml

parameters:
    intaro.twig_sandbox.policy_tags:
        - 'do'
        - 'for'
        - 'if'
        - 'spaceless'
```

### Filters

Default list of the allowed filters:
```yml
- 'abs'
- 'batch'
- 'capitalize'
- 'convert_encoding'
- 'date'
- 'date_modify'
- 'default'
- 'escape'
- 'first'
- 'format'
- 'join'
- 'json_encode'
- 'keys'
- 'last'
- 'length'
- 'lower'
- 'merge'
- 'nl2br'
- 'number_format'
- 'raw'
- 'replace'
- 'reverse'
- 'slice'
- 'sort'
- 'split'
- 'striptags'
- 'title'
- 'trim'
- 'upper'
- 'url_encode'
```

You can override list in the parameter `intaro.twig_sandbox.policy_filters`:
```yml
# app/config/config.yml

parameters:
    intaro.twig_sandbox.policy_filters:
        - 'sort'
        - 'upper'
        - 'sort'
```

### Functions

Default list of the allowed functions:
```yml
- 'attribute'
- 'constant'
- 'cycle'
- 'date'
- 'random'
- 'range'
```

You can override list in parameter `intaro.twig_sandbox.policy_functions`:
```yml
# app/config/config.yml

parameters:
    intaro.twig_sandbox.policy_functions:
        - 'date'
        - 'range'
```

### Allowed types

Default list of allowed return types:
```yml
- 'bool'
- 'collection'
- 'date'
- 'float'
- 'int'
- 'object'
- 'string'
```

You can override list in parameter `intaro.twig_sandbox.sandbox_annotation.value_types`:
```yml
# app/config/config.yml

parameters:
    intaro.twig_sandbox.sandbox_annotation.value_types:
        - 'string'
        - 'date'
        - 'collection'
        - 'stdClass'
```

### Environment

You can set twig environment parameters:
```php

$twig = $this->get(EnvironmentBuilder::class)->getSandboxEnvironment([
    'strict_variables' => true
]);
```

Also you might want to add extensions to your twig environment. Example how to add:
```php
// Acme/DemoBundle/AcmeDemoBundle.php
<?php

namespace Acme\DemoBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Acme\DemoBundle\DependencyInjection\Compiler\TwigSandboxPass;

class AcmeDemoBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new TwigSandboxPass());
    }
}
```

```php
// Acme/DemoBundle/DependencyInjection/Compiler/TwigSandboxPass.php
<?php

namespace Acme\DemoBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Intaro\TwigSandboxBundle\Builder\EnvironmentBuilder;

class TwigSandboxPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(EnvironmentBuilder::class)) {
            return;
        }

        $sandbox = $container->getDefinition(EnvironmentBuilder::class);
        $sandbox->addMethodCall('addExtension', [new Reference('acme_demo.twig_extension')]);
    }
}
```

## Development ##

### Run tests ###

Install vendors:
```shell
make vendor
```

Run php-cs-fixer, phpstan and phpunit:
```shell
make check
```
