<?php

namespace AlterPHP\EasyAdminExtensionBundle\Configuration;

use EasyCorp\Bundle\EasyAdminBundle\Configuration\ConfigPassInterface;

/**
 * Initializes the configuration for all the views of each entity, which is
 * needed when some entity relies on the default configuration for some view.
 */
class EmbeddedListSortConfigPass implements ConfigPassInterface
{
    public function process(array $backendConfig)
    {
        $backendConfig = $this->processSortingConfig($backendConfig);

        return $backendConfig;
    }

    /**
     * @param array $backendConfig
     *
     * @return array
     */
    private function processSortingConfig(array $backendConfig)
    {
        foreach ($backendConfig['entities'] as $entityName => $entityConfig) {
            if (
                !isset($entityConfig['embeddedList']['sort'])
                && isset($entityConfig['list']['sort'])
            ) {
                $backendConfig['entities'][$entityName]['embeddedList']['sort'] = $entityConfig['list']['sort'];
            }
        }

        return $backendConfig;
    }
}
