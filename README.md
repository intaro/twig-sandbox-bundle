# TwigSandboxBundle

There is [Twig](http://twig.sensiolabs.org)-extension [Twig_Extension_Sandbox](http://twig.sensiolabs.org/doc/api.html#sandbox-extension) which can be used to evaluate untrusted code and where access to unsafe attributes and methods is prohibited. This bundle allows to configure security policy for sandbox.

## Installation

TwigSandboxBundle requires Symfony 2.1 or higher.

Require the bundle in your `composer.json` file:

````json
{
    "require": {
        "intaro/twig-sandbox-bundle": "dev-master",
    }
}
```

Register the bundle in `AppKernel`:

```php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        //...

        new Intaro\TwigSandboxBundle\IntaroTwigSandboxBundle(),
);

    //...
}
```

Install the bundle:

```
$ composer update intaro/twig-sandbox-bundle
```

## Usage

Define allowed properties and methods for your entities throw annotation `@Sandbox`.

```php
<?php
// Acme/DemoBundle/Entity/Product.php

namespace Intaro\CRMBundle\Entity;

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
     * @Sandbox
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
     * @Sandbox
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

$twig = $this->get('intaro.twig_sandbox.builder')->getSandboxEnvironment();

$product = new Product();
$product->setName('Product 1');
$product->setQuantity(5);

//success render
$html1 = $twig->render(
    'Product {{ product.name }}',
    array(
        'product' => $product,
    )
);

//render with exception
$html2 = $twig->render(
    'Product {{ product.name }} in the quantity {{ product.quantity }}',
    array(
        'product' => $product,
    )
);

```

## Configure

### Methods and properties

You can defined allowed entities methods and properties throw annotation `Intaro\TwigSandboxBundle\Annotation\Sandbox`. Example above.

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
