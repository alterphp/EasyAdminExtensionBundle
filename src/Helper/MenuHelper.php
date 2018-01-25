<?php

namespace AlterPHP\EasyAdminExtensionBundle\Helper;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @author Pierre-Charles Bertineau <pc.bertineau@alterphp.com>
 */
class MenuHelper
{
    protected $tokenStorage;
    protected $authorizationChecker;

    public function __construct(TokenStorageInterface $tokenStorage, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
    }

    public function pruneMenuItems(array $menuConfig)
    {
        $menuConfig = $this->pruneAccessDeniedEntries($menuConfig);
        $menuConfig = $this->pruneEmptyFolderEntries($menuConfig);

        return $menuConfig;
    }

    protected function pruneAccessDeniedEntries(array $menuConfig)
    {
        foreach ($menuConfig as $key => $entry) {
            if (
                isset($entry['role'])
                && is_string($entry['role'])
                && (
                    null === $this->tokenStorage->getToken() || !$this->authorizationChecker->isGranted($entry['role'])
                )
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
