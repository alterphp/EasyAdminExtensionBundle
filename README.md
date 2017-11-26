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

### Step 3: Replace EasyAdmin controller

Instead of loading routes from EasyAdminBundle AdminController, load them from __EasyAdminExtensionBundle__ AdminController.

```yaml
# app/config/routing.yml
easy_admin_bundle:
    resource: "@EasyAdminExtensionBundle/Controller/AdminController.php"
    type:     annotation
    prefix:   /admin

# ...
```

If you have defined your own admin controllers, make them extend EasyAdminExtensionBundle admin controller.

Features
--------

### Filter list and search on request parameters

* EasyAdmin allows filtering list with `dql_filter` configuration entry. But this is not dynamic and must be configured as an apart list in `easy_admin` configuration.*

This extension allows to __dynamically filter lists__ by adding `filters` parameter in the URL parameters. Having a list of books at URL `<url-to-admin>?action=list&entity=Book` with a releaseYear fiekd, you can filter on books releasd in 2016 by requesting `<url-to-admin>?action=list&entity=Book&filters[entity.releaseDate]=2016`. It only matches exact values, but you can chain them. To request books released in 2015 and 2016, you must request `<url-to-admin>?action=list&entity=Book&filters[entity.releaseDate][]=2015&filters[entity.releaseDate][]=2016`.

This `filters` parameter is transmitted to the referer used for post update/delete/create redirection AND for search !

### Register your own form types with a short name (aliasing form types)

You have custom form types that you want to use in the EasyAdmin configuration. You can already register them with FQCN ... but it's quite boring and makes the admin massively enlarged. This feature allows you to define your own form types with short names, by configuration.

Let's see how to register them with those 2 examples (enum and statusable) :

```yaml
easy_admin_extension:
    custom_form_types:
        enum: Admin\Form\Type\EnumType
        statusable: Admin\Form\Type\StatusableType

```

### Embed lists as form widgets

Embed your EasyAdmin lists in edit views. This is really useful for \*ToMany relations.

Use pre-configured type `embedded_list` in the form definition :

```yaml
easy_admin:
    entities:
        Event:
            class: Tm\EventBundle\Entity\Event
        Promoter:
            class: AppBundle\Entity\Promoter
            form:
                fields:
                    # ...
                    - { type: group, label: Events, css_class: 'col-sm-12', icon: calendar }
                    - { property: events, label: '', type: embedded_list, type_options: { entity: Event, filters: { 'entity.promoter': 'form:parent.data.id' } } }

```

Let's see the result !

![Embedded list example](/doc/res/img/embedded-list.png)


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
