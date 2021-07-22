EasyAdmin Extension
===================

[![Latest Stable Version](https://poser.pugx.org/alterphp/easyadmin-extension-bundle/v/stable)](https://packagist.org/packages/alterphp/easyadmin-extension-bundle) [![Build Status](https://travis-ci.org/alterphp/EasyAdminExtensionBundle.svg?branch=master)](https://travis-ci.org/alterphp/EasyAdminExtensionBundle) [![SensioLabsInsight](https://insight.symfony.com/projects/a7179df5-4ed7-468c-899c-891535dbe802/mini.svg)](https://insight.sensiolabs.com/projects/a7179df5-4ed7-468c-899c-891535dbe802) [![Coverage Status](https://coveralls.io/repos/github/alterphp/EasyAdminExtensionBundle/badge.svg?branch=master)](https://coveralls.io/github/alterphp/EasyAdminExtensionBundle?branch=master) [![MIT Licensed](https://poser.pugx.org/alterphp/easyadmin-extension-bundle/license)](https://packagist.org/packages/alterphp/easyadmin-extension-bundle)

EasyAdmin Extension provides some useful extensions to [EasyAdmin](https://github.com/EasyCorp/EasyAdminBundle) admin generator for Symfony.

* Branch `3.x` of this bundle requires at least __PHP 7.1__ and __Symfony 4.2__ components or stack and is suitable for EasyAdmin `^2.2.2` (Versions v2.2.0 and v2.2.1 are not allowed as they don't have native menu permissions). It allows installation of EasyAdmin `2.2.0` or upper and Symfony 5 as well. __Extension bundle implementation of list filters is NOT COMPATIBLE with EasyAdmin dynamic list filters !__ That's why we introduced the following change :
    > :exclamation: __BC BREAK__ list filters implemented by this extension bundle now use `ext_filters` query/form parameter, as `filters` is now used by native EasyAdmin for its own implementation of dynamic list filters.

* Branch `2.x` of this bundle requires at least __PHP 7.1__ and __Symfony 4.1__ components or stack and is suitable for EasyAdmin `2.0.x` and `2.1.x`. __It does not allow installation of EasyAdmin `2.2.0` or upper !__

* Branch `1.x` of this bundle requires at least __PHP 7.0__ and __Symfony 3.0__ components or stack and is suitable for EasyAdmin `1.x`.



__Features__

* [List filters form](#list-filters-form)
* [Register its own form types and aliases](#register-your-own-form-types-with-a-short-name-aliasing-form-types)
* [Embed lists in EDIT and SHOW views](#embed-lists-in-edit-and-show-views)
* [Autocomplete option to create related entity](#autocomplete-add-new-option-create-for-modal-in-new-and-edit)
* [Role based access permissions](#define-access-permissions)
* [Confirmation modal for custom POST actions (without form)](#confirmation-modal-for-custom-post-actions-without-form)
* [Form fields by exclusion](#exclude-fields-in-forms)
* [Vertical theme for SHOW view](#use-template-show-vertical-boostrap)

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

Instead of loading routes from EasyAdminBundle EasyAdminController, load them from __EasyAdminExtensionBundle__ EasyAdminController.

```yaml
# config/routes/easy_admin.yaml
easy_admin_bundle:
    resource: '@EasyAdminExtensionBundle/Controller/EasyAdminController.php'
    type:     annotation
    prefix:   /admin

# ...
```

If you have defined your own admin controllers, make them extend EasyAdminExtensionBundle EasyAdminController.

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
     * @var int
     *
     * @ORM\Column(type="integer", nullable=false)
     */
    private $maxSubscriptions;

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

Define your filters under `list`.`form_filters` entity configuration

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

#### Automatic list filter guesser

Guesser for list form filters are based on mapped entity property :
* _boolean_: guessed filter is a choice list (null, Yes, No)
* _string_: guessed filter is multiple choice list that requires either `choices` (value/label array) or `choices_static_callback` (static callback from entity class returning a value/label array) in `type_options`.
* _integer_, _smallint_, _bigint_: guessed filter is an integer input
* _decimal_, _float_: guessed filter is a number input
* _*-to-one-relation_: guessed filter is a multiple autocomplete of relation target entity.

Filters form's method is GET and submitted through `form_filter` parameter. It is transmitted to the referer used for post update/delete/create redirection AND for search !

#### List filter operator

By default, list filter use `equals` operator or `in` for multiple value filters.

But you can use more operators with the `operator` attribute :

```yaml
entities:
        Animation:
            class: App\Entity\Animation
            list:
                form_filters:
                    - { name: maxSubscriptionGTE, property: maxSubscriptions, label: 'Max subscriptions >=', operator: gte }
                    - { name: maxSubscriptionLTE, property: maxSubscriptions, label: 'Max subscriptions <=', operator: lte }
```

Available built-in operators are listed in `AlterPHP\EasyAdminExtensionBundle\Model\ListFilter` class, as constant `OPERATOR_*` :
* __equals__: Is equal to
* __not__: Is different of
* __in__: Is in (`array` or Doctrine `Collection` expected)
* __notin__:  Is not in (`array` or Doctrine `Collection` expected)
* __gt__: Is greater than
* __gte__: Is greater than or equal to
* __lt__: Is lower than
* __lte__: Is lower than or equal to
* __like__: Is LIKE %filterValue%


### Filter list and search on request parameters

* EasyAdmin allows filtering list with `dql_filter` configuration entry. But this is not dynamic and must be configured as an apart list in `easy_admin` configuration.*

This extension allows to __dynamically filter lists__ by adding `ext_filters` parameter in the URL parameters. Having a list of books at URL `<url-to-admin>?action=list&entity=Book` with a releaseYear field, you can filter on books releasd in 2016 by requesting `<url-to-admin>?action=list&entity=Book&ext_filters[entity.releaseDate]=2016`. It only matches exact values, but you can chain them. To request books released in 2015 and 2016, you must request `<url-to-admin>?action=list&entity=Book&ext_filters[entity.releaseDate][]=2015&ext_filters[entity.releaseDate][]=2016`.

This `ext_filters` parameter is transmitted to the referer used for post update/delete/create redirection AND for search !

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

#### Options

Embedded lists are useful to show relations to an object in its *NEW/EDIT/FORM* or *SHOW* view. It relies on the *LIST* view of the related objects you want to embed in the parent EDIT/SHOW view. Options must be defined in `type_options` key for a *NEW/EDIT/FORM* view, or in `template_options` for a *SHOW* view.

Available options are :

- `entity`/`document`: Entity/Document config name (key under the EasyAdmin `entities`/`documents` config)
- `ext_filters`: Request filters to apply on the list
- `hidden_fields`: List of fields (columns) to hide from list fields config
- `max_results`: Number of items par page (list.max_results config is used if not defined)
- `sort`: Sort to apply
- `parent_object_fqcn`: Parent object FQCN in order to guess default filters (only when embedded in *SHOW* view, almost never required)
- `parent_object_property`: Matching property name on parent object FQCN (only when embedded in *SHOW* view, if `property` is not an ORM/ODM field)
- `object_fqcn`: Listed entities FQCN in order to guess default filters (only when embedded in *SHOW* view, almost never required)

#### Options guesser based on ORM metadata (for entities only)

Service EmbeddedListHelper is intended to guess `entity` entry for embedded_list. It's reads ORM metadata, based on parent entity (the one that embeds the list) and property name.

It also guess default filter (relation between the parent and embedded list) for most of cases.

#### Edit view

Use pre-configured type `embedded_list` in the form definition :

```yaml
easy_admin:
    entities:
        Event:
            class: App\Entity\Event
        Promoter:
            class: App\Entity\Promoter
            form:
                fields:
                    # ...
                    - { type: group, label: Events, css_class: 'col-sm-12', icon: calendar }
                    - { property: events, label: '', type: embedded_list, type_options: { entity: Event, ext_filters: { 'entity.promoter': 'form:parent.data.id' } } }

```

But in many cases, the EmbeddedListHelper guesses __type_options__ for you, and you just have to write :

```yaml
easy_admin:
    entities:
        Event:
            class: App\Entity\Event
        Promoter:
            class: App\Entity\Promoter
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
            class: App\Entity\Event
        Promoter:
            class: App\Entity\Promoter
            show:
                fields:
                    # ...
                    - { property: events, label: '', type: embedded_list }

```

Use following __template_options__ to pass options.

#### Disabling "Open in new tab" link at the bottom of embedded lists

* __globally__ in `config/packages/easy_admin_extension.yaml` :

    ```yaml
        easy_admin_extension:
            embedded_list:
                open_new_tab: false
    ```

* __by entity__ in `config/packages/easy_admin.yaml` :

    ```yaml
        easy_admin:
            entities:
                YourEntity:
                    class: App\Entity\YourEntity
                    embeddedList:
                        open_new_tab: false
    ```

### Autocomplete add new option 'create' for modal in new and edit

#### Configure form type 'easyadmin_autocomplete', add type_options: { attr: { create: true } }

```yaml
easy_admin:
    entities:
        Promoter:
            class: App\Entity\Promoter
        Event:
            class: App\Entity\Event
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

You can also define role permissions per entity field in a form:

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

To keep the confirmation modal behavior while creating [a custom action template](https://symfony.com/doc/2.x/bundles/EasyAdminBundle/tutorials/custom-actions.html#custom-templates-for-actions) you need to use the action template provided by this bundle, replacing ` {{ include('@EasyAdmin/default/action.html.twig') }}
` by ` {{ include('@EasyAdminExtension/default/action.html.twig') }}`.

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

    docker-compose run --rm php /app/vendor/bin/phpstan analyse -c /app/phpstan.neon --level=5 /app/src

License
-------

This software is published under the [MIT License](LICENSE.md)
