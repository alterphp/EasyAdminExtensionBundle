<?php

namespace AlterPHP\EasyAdminExtensionBundle\Configuration;

use EasyCorp\Bundle\EasyAdminBundle\Configuration\ConfigPassInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @author Pierre-Charles Bertineau <pc.bertineau@alterphp.com>
 */
class MenuConfigPass implements ConfigPassInterface
{
    protected $authorizationChecker;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    public function process(array $backendConfig)
    {
        $menuConfig = $backendConfig['design']['menu'];

        $menuConfig = $this->pruneAccessDeniedEntries($menuConfig);
        $menuConfig = $this->pruneEmptyFolderEntries($menuConfig);

        $backendConfig['design']['menu'] = $menuConfig;

        return $backendConfig;
    }

    protected function pruneAccessDeniedEntries(array $menuConfig)
    {
        foreach ($menuConfig as $key => $entry) {
            if (
                isset($entry['role'])
                && is_string($entry['role'])
                && !$this->authorizationChecker->isGranted($entry['role'])
            ) {
                unset($menuConfig[$key]);
                continue;
            }

            if (isset($entry['children']) && is_array($entry['children'])) {
                $menuConfig[$key]['children'] = $this->pruneAccessDeniedEntries($entry['children']);
            }
        }

        return $menuConfig;
    }

    protected function pruneEmptyFolderEntries(array $menuConfig)
    {
        foreach ($menuConfig as $key => $entry) {
            // Starts with sub-nodes in order to empty after possible children pruning...
            $menuConfig[$key]['children'] = $this->pruneEmptyFolderEntries($entry['children']);

            if ('empty' === $entry['type'] && empty($entry['children'])) {
                unset($menuConfig[$key]);
                continue;
            }
        }

        return $menuConfig;
    }
}
