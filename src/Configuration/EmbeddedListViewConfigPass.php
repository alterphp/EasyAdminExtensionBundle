<?php

namespace AlterPHP\EasyAdminExtensionBundle\Configuration;

use EasyCorp\Bundle\EasyAdminBundle\Configuration\ConfigPassInterface;

/**
 * Initializes the configuration for all the views of each entity, which is
 * needed when some entity relies on the default configuration for some view.
 */
class EmbeddedListViewConfigPass implements ConfigPassInterface
{
    private $defaultOpenNewTab;

    public function __construct($defaultOpenNewTab)
    {
        $this->defaultOpenNewTab = $defaultOpenNewTab;
    }

    public function process(array $backendConfig)
    {
        $backendConfig = $this->processSortingConfig($backendConfig);
        $backendConfig = $this->processOpenNewTabConfig($backendConfig);

        return $backendConfig;
    }

    /**
     * @param array $backendConfig
     *
     * @return array
     */
    private function processOpenNewTabConfig(array $backendConfig)
    {
        foreach ($backendConfig['entities'] as $entityName => $entityConfig) {
            if (!isset($entityConfig['embeddedList']['open_new_tab'])) {
                $backendConfig['entities'][$entityName]['embeddedList']['open_new_tab'] = $this->defaultOpenNewTab;
            }
        }

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
            } elseif (isset($entityConfig['embeddedList']['sort'])) {
                $sortConfig = $entityConfig['embeddedList']['sort'];
                if (!\is_string($sortConfig) && !\is_array($sortConfig)) {
                    throw new \InvalidArgumentException(sprintf('The "sort" option of the "embeddedList" view of the "%s" entity contains an invalid value (it can only be a string or an array).', $entityName));
                }

                if (\is_string($sortConfig)) {
                    $sortConfig = array('field' => $sortConfig, 'direction' => 'DESC');
                } else {
                    $sortConfig = array('field' => $sortConfig[0], 'direction' => strtoupper($sortConfig[1]));
                }

                $backendConfig['entities'][$entityName]['embeddedList']['sort'] = $sortConfig;
            }
        }

        return $backendConfig;
    }
}
