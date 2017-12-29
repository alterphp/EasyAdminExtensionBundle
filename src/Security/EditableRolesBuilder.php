<?php

declare(strict_types=1);

namespace AlterPHP\EasyAdminExtensionBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchy;

/**
 * This file is part of the EasyAdmin Extension package.
 *
 * It is much inspired from SonataUserBundle EditableRolesBuilder, without translation, admin roles nor pool.
 *
 * @see https://github.com/sonata-project/SonataUserBundle/blob/master/src/Security/EditableRolesBuilder.php
 */
class EditableRolesBuilder
{
    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var AuthorizationCheckerInterface
     */
    protected $authorizationChecker;

    /**
     * @var array
     */
    protected $rolesHierarchy;

    /**
     * @param TokenStorageInterface         $tokenStorage
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param array                         $rolesHierarchy
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorizationChecker,
        array $rolesHierarchy = array()
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
        $this->rolesHierarchy = new RoleHierarchy($rolesHierarchy);
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        $roles = array();

        if (!$this->tokenStorage->getToken()) {
            return $roles;
        }

        // get roles from the service container
        $reachableRoles = $this->rolesHierarchy->getReachableRoles($this->tokenStorage->getToken()->getRoles());

        foreach ($reachableRoles as $role) {
            $roles[] = $role->getRole();
        }

        return $roles;
    }
}
