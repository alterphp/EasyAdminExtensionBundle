<?php

declare(strict_types=1);

namespace AlterPHP\EasyAdminExtensionBundle\Helper;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * This file is part of the EasyAdmin Extension package.
 *
 * It is much inspired from SonataUserBundle EditableRolesHelper, without translation, admin roles nor pool.
 *
 * @see https://github.com/sonata-project/SonataUserBundle/blob/master/src/Security/EditableRolesHelper.php
 */
class EditableRolesHelper
{
    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var array
     */
    protected $roleHierarchy;

    /**
     * @param TokenStorageInterface $tokenStorage
     * @param array                 $roleHierarchy
     */
    public function __construct(TokenStorageInterface $tokenStorage, array $roleHierarchy = array())
    {
        $this->tokenStorage = $tokenStorage;
        $this->roleHierarchy = $roleHierarchy;
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

        $roles = $this->roleHierarchy;

        if (isset($roles['ROLE_SUPER_ADMIN'])) {
            unset($roles['ROLE_SUPER_ADMIN']);
        }

        $roles = array_map(function ($rolesGroup) {
            if (\is_array($rolesGroup)) {
                $rolesGroup = array_combine($rolesGroup, $rolesGroup);
            }

            return $rolesGroup;
        }, $roles);

        return $roles;
    }
}
