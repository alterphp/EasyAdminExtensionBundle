<?php

namespace AlterPHP\EasyAdminExtensionBundle\Security;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class AdminAuthorizationChecker
{
    private $authorizationChecker;
    private $adminMinimumRole;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker, string $adminMinimumRole = null)
    {
        $this->authorizationChecker = $authorizationChecker;
        $this->adminMinimumRole = $adminMinimumRole;
    }

    /**
     * Throws an error if user has no access to the entity action.
     *
     * @param array  $entityConfig
     * @param string $actionName
     * @param mixed  $subject
     */
    public function checksUserAccess(array $entityConfig, string $actionName, $subject = null)
    {
        if ($this->adminMinimumRole && !$this->authorizationChecker->isGranted($this->adminMinimumRole)) {
            throw new AccessDeniedException(
                \sprintf(
                    'You must be granted one of following role(s) [%s] to access admin !',
                    \is_array($this->adminMinimumRole) ? \implode('|', $this->adminMinimumRole) : $this->adminMinimumRole
                )
            );
        }

        $requiredRole = $this->getRequiredRole($entityConfig, $actionName);

        if ($requiredRole && !$this->authorizationChecker->isGranted($requiredRole, $subject)) {
            throw new AccessDeniedException(
                \sprintf(
                    'You must be granted one of following role(s) [%s] to perform this entity action !',
                    \is_array($requiredRole) ? \implode('|', $requiredRole) : $requiredRole
                )
            );
        }
    }

    /**
     * Returns user access as boolean, no exception thrown.
     *
     * @param array  $entityConfig
     * @param string $actionName
     * @param mixed  $subject
     *
     * @return bool
     */
    public function isEasyAdminGranted(array $entityConfig, string $actionName, $subject = null)
    {
        try {
            $this->checksUserAccess($entityConfig, $actionName, $subject);
        } catch (AccessDeniedException $e) {
            return false;
        }

        return true;
    }

    protected function getRequiredRole(array $entityConfig, string $actionName)
    {
        // Prevent from security breach: role for 'list' action was not required for 'List' nor 'LIST'...
        $actionName = \strtolower($actionName);

        if (isset($entityConfig[$actionName]) && isset($entityConfig[$actionName]['role'])) {
            return $entityConfig[$actionName]['role'];
        } elseif (isset($entityConfig['role_prefix'])) {
            return $entityConfig['role_prefix'].'_'.\strtoupper($actionName);
        }

        return $entityConfig['role'] ?? null;
    }
}
