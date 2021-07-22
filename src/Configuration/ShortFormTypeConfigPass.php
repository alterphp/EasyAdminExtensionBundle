<?php

namespace AlterPHP\EasyAdminExtensionBundle\Configuration;

use AlterPHP\EasyAdminExtensionBundle\Form\Type\EasyAdminEmbeddedListType;
use AlterPHP\EasyAdminExtensionBundle\Form\Type\ListFilterType;
use AlterPHP\EasyAdminExtensionBundle\Form\Type\Security\AdminRolesType;
use EasyCorp\Bundle\EasyAdminBundle\Configuration\ConfigPassInterface;
use EasyCorp\Bundle\EasyAdminBundle\Form\Util\FormTypeHelper;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Generalization of short form types for :
 *     - EasyAdminExtension bundle types
 *     - Custom form types
 *
 * @author Pierre-Charles Bertineau <pc.bertineau@alterphp.com>
 */
class ShortFormTypeConfigPass implements ConfigPassInterface
{
    private $customFormTypes = [];

    private static $configWithFormPaths = ['[form][fields]', '[edit][fields]', '[new][fields]', '[list][form_filters]'];
    private static $nativeShortFormTypes = [
        'embedded_list' => EasyAdminEmbeddedListType::class,
        'admin_roles' => AdminRolesType::class,
        'list_filter' => ListFilterType::class,
    ];

    public function __construct(array $customFormTypes = [])
    {
        $this->customFormTypes = $customFormTypes;
    }

    public function process(array $backendConfig)
    {
        $backendConfig = $this->replaceShortNameTypes($backendConfig);

        return $backendConfig;
    }

    private function replaceShortNameTypes(array $backendConfig)
    {
        if (isset($backendConfig['entities']) && \is_array($backendConfig['entities'])) {
            foreach ($backendConfig['entities'] as &$entityConfig) {
                $entityConfig = $this->replaceShortFormTypesInObjectConfig($entityConfig);
            }
        }

        if (isset($backendConfig['documents']) && \is_array($backendConfig['documents'])) {
            foreach ($backendConfig['documents'] as &$documentConfig) {
                $documentConfig = $this->replaceShortFormTypesInObjectConfig($documentConfig);
            }
        }

        return $backendConfig;
    }

    private function replaceShortFormTypesInObjectConfig(array $objectConfig)
    {
        $shortFormTypes = $this->getShortFormTypes();

        foreach (static::$configWithFormPaths as $configWithFormPath) {
            $propertyAccessor = PropertyAccess::createPropertyAccessor();
            $configPathItem = $propertyAccessor->getValue($objectConfig, $configWithFormPath);

            if (null !== $configPathItem && \is_array($configPathItem)) {
                foreach ($configPathItem as $name => $field) {
                    if (!isset($field['type'])) {
                        continue;
                    }

                    if (\array_key_exists($field['type'], $shortFormTypes)) {
                        $configPathItem[$name]['type'] = $shortFormTypes[$field['type']];
                    } elseif (self::isEasyAdminFormShortType($field['type'])) {
                        $configPathItem[$name]['type'] = FormTypeHelper::getTypeClass($field['type']);
                    }
                }

                $propertyAccessor->setValue($objectConfig, $configWithFormPath, $configPathItem);
            }

            unset($propertyAccessor);
        }

        return $objectConfig;
    }

    private static function isEasyAdminFormShortType(string $shortType)
    {
        $legacyEasyAdminMatchingType = FormTypeHelper::getTypeClass($shortType);

        return \class_exists($legacyEasyAdminMatchingType);
    }

    private function getShortFormTypes()
    {
        return \array_merge(static::$nativeShortFormTypes, $this->customFormTypes);
    }
}
