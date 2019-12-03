# UPGRADE guide for EasyAdminExtension bundle

## v3.0.0

This version unlocks dependency to EasyAdmin by allowing __EasyAdmin ^2.2.0__. As this version of EasyAdmin introduce its own implementation of list filters (using query parameter `filter`), and considering it is not compatible with extension bundle's implementation : __I decided to rename the parameter to pass filters used by the extension__. This is an important BC BREAK.

### Renamed query parameter for list filters (BC break)

`filter` is now used by native EasyAdmin implementation. Use `ext_filters` for extension implementation (for embedded lists) !

__BEFORE__

`<url-to-admin>?action=list&entity=Book&filters[entity.releaseDate]=2016`

__AFTER__

`<url-to-admin>?action=list&entity=Book&ext_filters[entity.releaseDate]=2016`


__BEFORE__

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
                    - { property: events, label: '', type: embedded_list, type_options: { entity: Event, filters: { 'entity.promoter': 'form:parent.data.id' } } }

```

__AFTER__

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
