EasyAdmin Extension
===================

[![Build Status](https://travis-ci.org/alterphp/EasyAdminExtensionBundle.svg?branch=master)](https://travis-ci.org/alterphp/EasyAdminExtensionBundle)

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
```

Features
--------

### Filter list and search on request parameters

*EasyAdmin allows filtering list with `dql_filter` configuration entry. But this is not dynamic and must be configured as an apart list in `easy_admin` configuration.*

This extension allows to __dynamically filter lists__ by adding `filters` parameter in the URL parameters. Having a list of books at URL `<url-to-admin>?action=list&entity=Book` with a releaseYear fiekd, you can filter on books releasd in 2016 by requesting `<url-to-admin>?action=list&entity=Book&filters[entity.releaseDate]=2016`. It only matches exact values, but you can chain them. To request books released in 2015 and 2016, you must request `<url-to-admin>?action=list&entity=Book&filters[entity.releaseDate][]=2015&filters[entity.releaseDate][]=2016`.

This `filters` parameter is transmitted to the referer used for post update/delete/create redirection AND for search !

Run tests
---------

Run following command :

```bash
$ ./vendor/phpunit/phpunit/phpunit
```

OR using Docker and Docker Compose :

```bash
$ docker-compose run --rm phpunit
```

License
-------

This software is published under the [MIT License](LICENSE.md)
