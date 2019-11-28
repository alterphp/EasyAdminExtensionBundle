# UPGRADE guide for EasyAdminExtension bundle

## v2.2.0

This version unlocks dependency to EasyAdmin by allowing __EasyAdmin ^2.2.0__. As this version of EasyAdmin introduce its own implementation of list filyters (using query parameter `filter`), and considering it is not compatible with extension bundle's implementation : __I decided to rename the parameter to pass filters used by the extension__. This is an important BC BREAK.

## v2.1.0

List filters form have been improved, with minor BC breaks :

* Label must be configured on the root level, not in the `type_options` attribiute.

__BEFORE__

```yaml
easy_admin:
    entities:
        MyEntity:
            class: App\Entity\MyEntity
            list:
                form_filters:
                    - { name: myFilter, property: status, type_options: { label: 'Filter on status' } }
```

__AFTER__

```yaml
easy_admin:
    entities:
        MyEntity:
            class: App\Entity\MyEntity
            list:
                form_filters:
                    - { name: myFilter, property: status, label: 'Filter on status' }
```
