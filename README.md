# TwigSandboxBundle #

There is [Twig](http://twig.sensiolabs.org)-extension [Twig_Extension_Sandbox](http://twig.sensiolabs.org/doc/api.html#sandbox-extension) which can be used to evaluate untrusted code and where access to unsafe attributes and methods is prohibited. This bundle allows to configure security policy for sandbox.

## Installation ##

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
        
        //...
);

    //...
}
```

Install the bundle:

```
$ composer update intaro/twig-sandbox-bundle
```
