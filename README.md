EasyAdmin Extension
===================

EasyAdmin Extension provides some useful extensions to EasyAdmin admin generator for Symfony.

:exclamation: This bundle requires at least __PHP 7.0__ and __Symfony 3.0__ components or stack.

Installation
------------

### Step 1: Download the Bundle

```bash
$ composer require alterphp/easyadmin-extension-bundle
```

This command requires you to have Composer installed globally, as explained
in the [Composer documentation](https://getcomposer.org/doc/00-intro.md).

### Step 2: Enable the Bundle

```php
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...
            new AlterPHP\EasyAdminExtensionBundle\EasyAdminExtensionBundle(),
        );
    }

    // ...
}

Features
--------

### Filter list and search on request parameters

License
-------

This software is published under the [MIT License](LICENSE.md)
