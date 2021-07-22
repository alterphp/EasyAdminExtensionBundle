<?php

namespace AlterPHP\EasyAdminExtensionBundle\Configuration;

use EasyCorp\Bundle\EasyAdminBundle\Configuration\ConfigPassInterface;

/**
 * Initializes the configuration for all the views of each object of type "%s", which is
 * needed when some object of type "%s" relies on the default configuration for some view.
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
        $backendConfig = $this->processTemplateConfig($backendConfig);
        $backendConfig = $this->processSortingConfig($backendConfig);
        $backendConfig = $this->processOpenNewTabConfig($backendConfig);

        return $backendConfig;
    }

    /**
     * @return array
     */
    private function processOpenNewTabConfig(array $backendConfig)
    {
        foreach (['entities', 'documents'] as $objectTypeRootKey) {
            if (isset($backendConfig[$objectTypeRootKey]) && \is_array($backendConfig[$objectTypeRootKey])) {
                foreach ($backendConfig[$objectTypeRootKey] as $objectName => $objectConfig) {
                    if (!isset($objectConfig['embeddedList']['open_new_tab'])) {
                        $backendConfig[$objectTypeRootKey][$objectName]['embeddedList']['open_new_tab'] = $this->defaultOpenNewTab;
                    }
                }
            }
        }

        return $backendConfig;
    }

    /**
     * @return array
     */
    private function processSortingConfig(array $backendConfig)
    {
        foreach (['entities', 'documents'] as $objectTypeRootKey) {
            if (isset($backendConfig[$objectTypeRootKey]) && \is_array($backendConfig[$objectTypeRootKey])) {
                foreach ($backendConfig[$objectTypeRootKey] as $objectName => $objectConfig) {
                    if (
                        !isset($objectConfig['embeddedList']['sort'])
                        && isset($objectConfig['list']['sort'])
                    ) {
                        $backendConfig[$objectTypeRootKey][$objectName]['embeddedList']['sort'] = $objectConfig['list']['sort'];
                    } elseif (isset($objectConfig['embeddedList']['sort'])) {
                        $sortConfig = $objectConfig['embeddedList']['sort'];
                        if (!\is_string($sortConfig) && !\is_array($sortConfig)) {
                            throw new \InvalidArgumentException(\sprintf('The "sort" option of the "embeddedList" view of the "%s" object contains an invalid value (it can only be a string or an array).', $objectName));
                        }

                        if (\is_string($sortConfig)) {
                            $sortConfig = ['field' => $sortConfig, 'direction' => 'DESC'];
                        } else {
                            $sortConfig = ['field' => $sortConfig[0], 'direction' => \strtoupper($sortConfig[1])];
                        }

                        $backendConfig[$objectTypeRootKey][$objectName]['embeddedList']['sort'] = $sortConfig;
                    }
                }
            }
        }

        return $backendConfig;
    }

    private function processTemplateConfig(array $backendConfig)
    {
        foreach (['entities', 'documents'] as $objectTypeRootKey) {
            if (isset($backendConfig[$objectTypeRootKey]) && \is_array($backendConfig[$objectTypeRootKey])) {
                foreach ($backendConfig[$objectTypeRootKey] as $objectName => $objectConfig) {
                    if (!isset($objectConfig['embeddedList']['template'])) {
                        $backendConfig[$objectTypeRootKey][$objectName]['embeddedList']['template'] = '@EasyAdminExtension/default/embedded_list.html.twig';
                    }
                }
            }
        }

        return $backendConfig;
    }
}
