<?php

namespace AlterPHP\EasyAdminExtensionBundle\Helper;

/**
 * @author Pierre-Charles Bertineau <pc.bertineau@alterphp.com>
 */
class MenuHelper
{
    protected $adminAuthorizationChecker;

    public function __construct($adminAuthorizationChecker)
    {
        $this->adminAuthorizationChecker = $adminAuthorizationChecker;
    }

    public function pruneMenuItems(array $menuConfig, array $entitiesConfig)
    {
        $menuConfig = $this->pruneAccessDeniedEntries($menuConfig, $entitiesConfig);
        $menuConfig = $this->pruneEmptyFolderEntries($menuConfig);

        return $menuConfig;
    }

    protected function pruneAccessDeniedEntries(array $menuConfig, array $entitiesConfig)
    {
        foreach ($menuConfig as $key => $entry) {
            // Checks role defined on entity action
            if (isset($entry['entity'])) {
                $action = $entry['params']['action'] ?? 'list';
                $entityConfig = $entitiesConfig[$entry['entity']];

                if (!$this->adminAuthorizationChecker->isEasyAdminGranted($entityConfig, $action)) {
                    unset($menuConfig[$key]);
                    continue;
                }
            }

            // Recursively prune children
            if (isset($entry['children']) && is_array($entry['children'])) {
                $menuConfig[$key]['children'] = $this->pruneAccessDeniedEntries($entry['children'], $entitiesConfig);
            }
        }

        return $menuConfig;
    }

    protected function pruneEmptyFolderEntries(array $menuConfig)
    {
        foreach ($menuConfig as $key => $entry) {
            if (isset($entry['children'])) {
                // Starts with sub-nodes in order to empty after possible children pruning...
                $menuConfig[$key]['children'] = $this->pruneEmptyFolderEntries($entry['children']);

                if ('empty' === $entry['type'] && empty($entry['children'])) {
                    unset($menuConfig[$key]);
                    continue;
                }
            }
        }

        return $menuConfig;
    }
}
