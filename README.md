EasyAdmin Extension
===================

[![Latest Stable Version](https://poser.pugx.org/alterphp/easyadmin-extension-bundle/v/stable)](https://packagist.org/packages/alterphp/easyadmin-extension-bundle) [![Build Status](https://travis-ci.org/alterphp/EasyAdminExtensionBundle.svg?branch=master)](https://travis-ci.org/alterphp/EasyAdminExtensionBundle) [![SensioLabsInsight](https://insight.sensiolabs.com/projects/a7179df5-4ed7-468c-899c-891535dbe802/mini.png)](https://insight.sensiolabs.com/projects/a7179df5-4ed7-468c-899c-891535dbe802) [![Coverage Status](https://coveralls.io/repos/github/alterphp/EasyAdminExtensionBundle/badge.svg?branch=master)](https://coveralls.io/github/alterphp/EasyAdminExtensionBundle?branch=master) [![License](https://poser.pugx.org/alterphp/easyadmin-extension-bundle/license)](https://packagist.org/packages/alterphp/easyadmin-extension-bundle)

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

Symfony 4 directory structure :
```yaml
# config/routes/easy_admin.yaml
easy_admin_bundle:
    resource: '@EasyAdminExtensionBundle/Controller/AdminController.php'
    type:     annotation
    prefix:   /admin

# ...
```

Former Symfony 2/3 directory structure :
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

### List filters form

Add filters on list views by configuration.

Consider following Animation entity using such [ValueListTrait](https://github.com/alterphp/components/blob/master/src/AlterPHP/Component/Behavior/ValueListTrait) :

```php
class Animation
{
    use ValueListTrait;

    /**
     * @var string
     *
     * @ORM\Id
     * @ORM\Column(type="guid")
     */
    private $id;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $enabled;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=31)
     */
    protected $status;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=31, nullable=false)
     */
    private $type;

    /**
     * @var Organization
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Organization", inversedBy="animations")
     * @ORM\JoinColumn(nullable=false)
     */
    private $organization;

    const STATUS_DRAFT = 'draft';
    const STATUS_PUBLISHED = 'published';
    const STATUS_OPEN = 'open';
    const STATUS_ACTIVE = 'active';
    const STATUS_CLOSED = 'closed';
    const STATUS_ARCHIVED = 'archived';
}
```

Define your filters under `list`.`form_filters` entity configuration. Automatic guesser set up a ChoiceType for filters mapped on boolean (NULL, true, false) and string class properties. ChoiceType for string properties requires either a `choices` label/value array in `type_options` of a `choices_static_callback` static callable that returns label/value choices list.


```yaml
easy_admin:
    entities:
        Animation:
            class: App\Entity\Animation
            list:
                form_filters:
                    - enabled
                    - { property: type, type_options: { choices: { Challenge: challenge, Event: event } } }
                    - { property: status, type_options: { choices_static_callback: [getValuesList, [status, true]] } }
                    - organization
```

Let's see the result !

![Embedded list example](/doc/res/img/list-form-filters.png)

### Filter list and search on request parameters

* EasyAdmin allows filtering list with `dql_filter` configuration entry. But this is not dynamic and must be configured as an apart list in `easy_admin` configuration.*

This extension allows to __dynamically filter lists__ by adding `filters` parameter in the URL parameters. Having a list of books at URL `<url-to-admin>?action=list&entity=Book` with a releaseYear field, you can filter on books releasd in 2016 by requesting `<url-to-admin>?action=list&entity=Book&filters[entity.releaseDate]=2016`. It only matches exact values, but you can chain them. To request books released in 2015 and 2016, you must request `<url-to-admin>?action=list&entity=Book&filters[entity.releaseDate][]=2015&filters[entity.releaseDate][]=2016`.

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

### Embed lists in edit and show views

#### Edit view

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

But in many cases, the EmbeddedListHelper guesses type_options for you, and you just have to write :

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
                    - { property: events, label: '', type: embedded_list }

```

Let's see the result !

![Embedded list example](/doc/res/img/embedded-list.png)

#### Show view

Using guesser for classic \*ToMany relations :

```yaml
easy_admin:
    entities:
        Event:
            class: Tm\EventBundle\Entity\Event
        Promoter:
            class: AppBundle\Entity\Promoter
            show:
                fields:
                    # ...
                    - { property: events, label: '', type: embedded_list }

```

Use following __template_options__ to build your own embedded list (see `field_embedded_list.html.twig`) : entity_fqcn, parent_entity_property, filters, entity, sort.

### Autocomplete add new option 'create' for modal in new and edit

#### Configure form type 'easyadmin_autocomplete', add type_options: { attr: { create: true } }

```yaml
easy_admin:
    entities:
        Promoter:
            class: AppBundle\Entity\Promoter
        Event:
            class: Tm\EventBundle\Entity\Event
            form:
                fields:
                    # ...
                    - { property: 'promoter', type: 'easyadmin_autocomplete', type_options: { attr: { create: true } } }

```

### Define access permissions

#### Global minimum role access

You can define a minimum role to access the EasyAdmin controller (any action handled by the controller) :

```yaml
easy_admin_extension:
    minimum_role: ROLE_ADMIN
```

This is just a global restriction, that should live with a security firewall as described in [Symfony documentation](https://symfony.com/doc/current/security.html).

#### Per entity action role permissions

You can also define role permissions per entity action :

```yaml
easy_admin:
    entities:
        Product:
            class: App\Entity\Product
            list:
                role: ROLE_ADMIN_PRODUCT_LIST
            search:
                role: ROLE_ADMIN_PRODUCT_SEARCH
            new:
                role: ROLE_ADMIN_PRODUCT_NEW
            edit:
                role: ROLE_ADMIN_PRODUCT_EDIT
            show:
                role: ROLE_ADMIN_PRODUCT_SHOW
            delete:
                role: ROLE_ADMIN_PRODUCT_DELETE
```

Above configuration define a required _role_ per action for *Product* entity. This is too verbose, isn't it ? Let's sum up as following :

```yaml
easy_admin:
    entities:
        Product:
            class: App\Entity\Product
            role_prefix: ROLE_ADMIN_PRODUCT
```

Entity _role_prefix_ defines all actions required roles by appending the action name to the prefix.

#### Per entity field role permissions in form

You can also define role permissions per entity field in form:

```yaml
easy_admin:
    entities:
        Product:
            class: App\Entity\Product
            form:
                fields:
                    - { property: enabled, role: ROLE_ADMIN }
```

If user do not hold the required role, the form field will be disabled.

### Confirmation modal for custom POST actions without form

A generic confirmation modal asks for confirmation (or any custom message) for links with `data-confirm` attribute (that may contain the custom message) and URL in `data-href` attribute.

Easy configurable with custom list actions by adding a `confirm` key :

```yaml
easyadmin:
    entities:
        User:
            list:
                actions:
                    - { name: disable, icon: ban, title: Disable user, label: false, target: _blank, confirm: User will lose any access to the platform ! }
```

### Exclude fields in forms

```yaml
easyadmin:
    entities:
        User:
            form:
                exclude_fields: ['references']
```

In such entity:

```php
<?php

class User
{
    public $name;

    public $title;

    public $references;
}
```

It will show all fields but those mentioned in `exclude_fields`, equivalent to the following configuration:

```yaml
easyadmin:
    entities:
        User:
            form:
                fields: ['name', 'title']
```

### Use template show vertical boostrap

Design EasyAdmin configuration:

```yaml
easy_admin:
    design:
        templates:
            show: '@EasyAdminExtension/default/show_vertical.html.twig'

```

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

Run code quality tools
----------------------

### PHP CS Fixer

Locally with Docker :

    docker-compose run --rm php /app/vendor/bin/php-cs-fixer fix --config=/app/.php_cs /app/src

### PHPStan

Locally with Docker :

    docker-compose run --rm php /app/vendor/bin/phpstan analyse -c /app/phpstan.neon --level=6 /app/src

License
-------

This software is published under the [MIT License](LICENSE.md)
