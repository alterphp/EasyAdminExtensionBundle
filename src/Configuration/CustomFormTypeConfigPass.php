<?php

namespace AlterPHP\EasyAdminExtensionBundle\Configuration;

use EasyCorp\Bundle\EasyAdminBundle\Configuration\ConfigPassInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Normalizes the different configuration formats available for entities, views,
 * actions and properties.
 *
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 */
class CustomFormTypeConfigPass implements ConfigPassInterface
{
    private $customFormTypes = array();
    private static $configWithFormKeys = array('form', 'edit', 'new');

    public function __construct(array $customFormTypes = array())
    {
        $this->customFormTypes = $customFormTypes;
    }

    public function process(array $backendConfig)
    {
        $backendConfig = $this->replaceShortNameTypes($backendConfig);

        return $backendConfig;
    }

    protected function replaceShortNameTypes(array $backendConfig)
    {
        if (
            empty($this->customFormTypes)
            || !isset($backendConfig['entities'])
            || !is_array($backendConfig['entities'])
        ) {
            return $backendConfig;
        }

        foreach ($backendConfig['entities'] as &$entity) {
            $entity = $this->replaceCustomTypesInEntityConfig($entity);
        }

        return $backendConfig;
    }

    protected function replaceCustomTypesInEntityConfig(array $entity)
    {
        foreach (static::$configWithFormKeys as $configWithFormKey) {
            if (
                isset($entity[$configWithFormKey])
                && isset($entity[$configWithFormKey]['fields'])
                && is_array($entity[$configWithFormKey]['fields'])
            ) {
                foreach ($entity[$configWithFormKey]['fields'] as $name => $field) {
                    if (!isset($field['type'])) {
                        continue;
                    }

                    if (in_array($field['type'], array_keys($this->customFormTypes))) {
                        $entity[$configWithFormKey]['fields'][$name]['type'] = $this->customFormTypes[$field['type']];
                    }
                }
            }
        }

        return $entity;
    }
}
