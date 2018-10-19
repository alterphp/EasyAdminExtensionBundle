<?php

namespace AlterPHP\EasyAdminExtensionBundle\Configuration;

use AlterPHP\EasyAdminExtensionBundle\Helper\EmbeddedListHelper;
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
     * @var EmbeddedListHelper
     */
    private $embeddedListHelper;

    private static $mapTypeToTemplates = array(
        // Use EasyAdminExtension namespace because EasyAdminTwigExtension checks namespaces
        // to detect custom templates.
        'embedded_list' => '@EasyAdminExtension/default/field_embedded_list.html.twig',
    );

    /**
     * ShowViewConfigPass constructor.
     *
     * @param EmbeddedListHelper $embeddedListHelper
     */
    public function __construct(EmbeddedListHelper $embeddedListHelper)
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
     * @param array $backendConfig
     *
     * @return array
     */
    private function processCustomShowTypes(array $backendConfig)
    {
        foreach ($backendConfig['entities'] as $entityName => $entityConfig) {
            foreach ($entityConfig['show']['fields'] as $fieldName => $fieldMetadata) {
                if (\array_key_exists($fieldMetadata['type'], static::$mapTypeToTemplates)) {
                    $template = $this->isFieldTemplateDefined($fieldMetadata)
                                    ? $fieldMetadata['template']
                                    : static::$mapTypeToTemplates[$fieldMetadata['type']];
                    $entityConfig['show']['fields'][$fieldName]['template'] = $template;

                    $entityConfig['show']['fields'][$fieldName]['template_options'] = $this->processTemplateOptions(
                        $fieldMetadata['type'], $fieldMetadata
                    );
                }
            }

            $backendConfig['entities'][$entityName] = $entityConfig;
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
                $parentEntityFqcn = $templateOptions['parent_entity_fqcn'] ?? $fieldMetadata['sourceEntity'];
                $parentEntityProperty = $templateOptions['parent_entity_property'] ?? $fieldMetadata['property'];
                $entityFqcn = $this->embeddedListHelper->getEntityFqcnFromParent(
                    $parentEntityFqcn, $parentEntityProperty
                );
                if (!isset($templateOptions['entity_fqcn'])) {
                    $templateOptions['entity_fqcn'] = $entityFqcn;
                }
                if (!isset($templateOptions['parent_entity_property'])) {
                    $templateOptions['parent_entity_property'] = $parentEntityProperty;
                }
                if (!isset($templateOptions['entity'])) {
                    $templateOptions['entity'] = $this->embeddedListHelper->guessEntityEntry($entityFqcn);
                }
                if (!isset($templateOptions['filters'])) {
                    $templateOptions['filters'] = [];
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
