<?php

namespace AlterPHP\EasyAdminExtensionBundle\Security;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class AdminAuthorizationChecker
{
    private $authorizationChecker;
    private $adminMinimumRole;

    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker, string $adminMinimumRole = null
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->adminMinimumRole = $adminMinimumRole;
    }

    public function checksUserAccess(array $entity, string $actionName, array $params = array())
    {
        if ($this->adminMinimumRole && !$this->authorizationChecker->isGranted($this->adminMinimumRole)) {
            throw new AccessDeniedException(
                sprintf('You must be granted %s role to access admin !', $this->adminMinimumRole)
            );
        }

        $requiredRole = $this->getRequiredRole($entity, $actionName, $params);
        if ($requiredRole && !$this->authorizationChecker->isGranted($requiredRole)) {
            throw new AccessDeniedException(
                sprintf('You must be granted %s role to perform this entity action !', $requiredRole)
            );
        }
    }

    protected function getRequiredRole(array $entity, string $actionName, array $params)
    {
        if (isset($entity[$actionName]) && isset($entity[$actionName]['role'])) {
            return $entity[$actionName]['role'];
        }

        return $entity['role'] ?? null;
    }
}

