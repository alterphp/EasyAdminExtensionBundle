<?php

namespace AlterPHP\EasyAdminExtensionBundle\Configuration;

use EasyCorp\Bundle\EasyAdminBundle\Configuration\ConfigPassInterface;

/**
 * Adds custom types for SHOW view :
 *     - Embedded lists
 *
 * @author Pierre-Charles Bertineau <pc.bertineau@alterphp.com>
 */
class ShowViewConfigPass implements ConfigPassInterface
{
    /**
     * @var \AlterPHP\EasyAdminExtensionBundle\Helper\EmbeddedListHelper
     */
    private $embeddedListHelper;

    private static $mapTypeToTemplates = [
        // Use EasyAdminExtension namespace because EasyAdminTwigExtension checks namespaces
        // to detect custom templates.
        'embedded_list' => '@EasyAdminExtension/default/field_embedded_list.html.twig',
    ];

    /**
     * ShowViewConfigPass constructor.
     *
     * @param \AlterPHP\EasyAdminExtensionBundle\Helper\EmbeddedListHelper $embeddedListHelper
     */
    public function __construct($embeddedListHelper)
    {
        $this->embeddedListHelper = $embeddedListHelper;
    }

    public function process(array $backendConfig)
    {
        $backendConfig = $this->processCustomShowTypes($backendConfig);

        return $backendConfig;
    }

    /**
     * Process custom types for SHOW view.
     *
     * @return array
     */
    private function processCustomShowTypes(array $backendConfig)
    {
        foreach (['entities', 'documents'] as $objectTypeRootKey) {
            if (isset($backendConfig[$objectTypeRootKey]) && \is_array($backendConfig[$objectTypeRootKey])) {
                foreach ($backendConfig[$objectTypeRootKey] as $objectName => $objectConfig) {
                    foreach ($objectConfig['show']['fields'] as $fieldName => $fieldMetadata) {
                        if (\array_key_exists($fieldMetadata['type'], static::$mapTypeToTemplates)) {
                            $template = $this->isFieldTemplateDefined($fieldMetadata)
                                            ? $fieldMetadata['template']
                                            : static::$mapTypeToTemplates[$fieldMetadata['type']];
                            $objectConfig['show']['fields'][$fieldName]['template'] = $template;

                            $objectConfig['show']['fields'][$fieldName]['template_options'] = $this->processTemplateOptions(
                                $fieldMetadata['type'], $fieldMetadata
                            );
                        }
                    }

                    $backendConfig[$objectTypeRootKey][$objectName] = $objectConfig;
                }
            }
        }

        return $backendConfig;
    }

    private function isFieldTemplateDefined(array $fieldMetadata)
    {
        return isset($fieldMetadata['template'])
               && '@EasyAdmin/default/label_undefined.html.twig' !== $fieldMetadata['template'];
    }

    private function processTemplateOptions(string $type, array $fieldMetadata)
    {
        $templateOptions = $fieldMetadata['template_options'] ?? [];

        switch ($type) {
            case 'embedded_list':
                // Deprecations
                if (isset($templateOptions['entity_fqcn']) && !isset($templateOptions['object_fqcn'])) {
                    $templateOptions['object_fqcn'] = $templateOptions['entity_fqcn'];
                    unset($templateOptions['entity_fqcn']);

                    \trigger_error(\sprintf('The "entity_fqcn" option for embedded_list is deprecated since version 1.4.0 and it will be removed in 2.0. Use the "object_fqcn" option instead.'), E_USER_DEPRECATED);
                }
                if (isset($templateOptions['parent_entity_fqcn']) && !isset($templateOptions['parent_object_fqcn'])) {
                    $templateOptions['parent_object_fqcn'] = $templateOptions['parent_entity_fqcn'];
                    unset($templateOptions['parent_entity_fqcn']);

                    \trigger_error(\sprintf('The "parent_entity_fqcn" option for embedded_list is deprecated since version 1.4.0 and it will be removed in 2.0. Use the "parent_object_fqcn" option instead.'), E_USER_DEPRECATED);
                }
                if (isset($templateOptions['parent_entity_property']) && !isset($templateOptions['parent_object_property'])) {
                    $templateOptions['parent_object_property'] = $templateOptions['parent_entity_property'];
                    unset($templateOptions['parent_entity_property']);

                    \trigger_error(\sprintf('The "parent_entity_property" option for embedded_list is deprecated since version 1.4.0 and it will be removed in 2.0. Use the "parent_object_property" option instead.'), E_USER_DEPRECATED);
                }

                $parentObjectFqcn = $templateOptions['parent_object_fqcn'] ?? $fieldMetadata['sourceEntity'];
                $parentObjectProperty = $templateOptions['parent_object_property'] ?? $fieldMetadata['property'];
                $objectFqcn = $this->embeddedListHelper->getEntityFqcnFromParent(
                    $parentObjectFqcn, $parentObjectProperty
                );

                if (isset($templateOptions['document'])) {
                    $templateOptions['object_type'] = 'document';
                } else {
                    $templateOptions['object_type'] = 'entity';
                }

                if (!isset($templateOptions['entity']) && !isset($templateOptions['document'])) {
                    $templateOptions['entity'] = $this->embeddedListHelper->guessEntityEntry($objectFqcn);
                }

                if (!isset($templateOptions['object_fqcn'])) {
                    $templateOptions['object_fqcn'] = $objectFqcn;
                }
                if (!isset($templateOptions['parent_object_property'])) {
                    $templateOptions['parent_object_property'] = $parentObjectProperty;
                }
                if (!isset($templateOptions['ext_filters'])) {
                    $templateOptions['ext_filters'] = [];
                }
                if (!isset($templateOptions['hidden_fields'])) {
                    $templateOptions['hidden_fields'] = [];
                }
                if (!isset($templateOptions['max_results'])) {
                    $templateOptions['max_results'] = null;
                }
                if (isset($templateOptions['sort'])) {
                    $sortOptions = $templateOptions['sort'];
                    $templateOptions['sort'] = [
                        'field' => $sortOptions[0],
                        'direction' => $sortOptions[1] ?? 'DESC',
                    ];
                } else {
                    $templateOptions['sort'] = null;
                }
                break;

            default:
                break;
        }

        return $templateOptions;
    }
}
