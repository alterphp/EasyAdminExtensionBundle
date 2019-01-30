# UPGRADE guide for EasyAdminExtension bundle

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
