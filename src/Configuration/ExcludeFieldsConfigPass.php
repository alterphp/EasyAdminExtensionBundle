<?php

namespace AlterPHP\EasyAdminExtensionBundle\Configuration;

use AlterPHP\EasyAdminExtensionBundle\Exception\ConflictingConfigurationException;
use EasyCorp\Bundle\EasyAdminBundle\Configuration\ConfigPassInterface;
use ReflectionClass;
use ReflectionProperty;

/**
 * Adds "exclude_fields" option to forms
 *
 * ```yaml
 * form:
 *     fields: ['name', 'perex', 'description', 'duration', 'capacity']
 *     ↓
 *     exclude_fields: ['trainingReferences']
 * ```
 */
class ExcludeFieldsConfigPass implements ConfigPassInterface
{
    /**
     * @param mixed[] $backendConfig
     *
     * @return mixed[]
     */
    public function process(array $backendConfig): array
    {
        if (!isset($backendConfig['entities'])) {
            return $backendConfig;
        }

        foreach ($backendConfig['entities'] as $entityName => $entityConfig) {
            if (!isset($entityConfig['form']['exclude_fields'])) {
                continue;
            }

            $this->ensureFieldConfigurationIsValid($entityConfig, $entityName);

            $propertyNames = $this->getPropertyNamesForEntity($entityConfig, $entityName);

            // filter fields to be displayed
            $fields = [];
            foreach ($propertyNames as $propertyName) {
                if ($this->shouldSkipField($propertyName, $entityConfig['form']['exclude_fields'])) {
                    continue;
                }

                $fields[] = $propertyName;
            }

            // set it!
            $backendConfig['entities'][$entityName]['form']['fields'] = $fields;
        }

        return $backendConfig;
    }

    /**
     * @param string[] $excludedFields
     */
    private function shouldSkipField(string $propertyName, array $excludedFields): bool
    {
        if ('id' === $propertyName) {
            return true;
        }

        return in_array($propertyName, $excludedFields, true);
    }

    /**
     * Explicit "fields" option and "exclude_fields" won't work together
     *
     * @param mixed[] $entityConfig
     */
    private function ensureFieldConfigurationIsValid(array $entityConfig, string $entityName)
    {
        if (!isset($entityConfig['form']['fields']) || !count($entityConfig['form']['fields'])) {
            return;
        }

        throw new ConflictingConfigurationException(sprintf(
            '"%s" and "%s" are mutually conflicting. Pick just one of them in %s YAML configuration',
            'exclude_fields',
            'fields',
            sprintf('easy_admin_bundle > entities > %s > form', $entityName)
        ));
    }

    /**
     * @param mixed[] $entityConfig
     *
     * @return string[]
     */
    private function getPropertyNamesForEntity(array $entityConfig, string $entityName): array
    {
        $entityClass = $entityConfig['class'] ?: $entityName;
        $entityReflectionClass = new ReflectionClass($entityClass);

        return array_map(function (ReflectionProperty $reflectionProperty) {
            return $reflectionProperty->getName();
        }, $entityReflectionClass->getProperties());
    }
}
