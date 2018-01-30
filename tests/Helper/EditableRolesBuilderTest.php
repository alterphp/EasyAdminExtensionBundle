<?php

namespace AlterPHP\EasyAdminExtensionBundle\Tests\Helper;

use AlterPHP\EasyAdminExtensionBundle\Helper\EditableRolesHelper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class EditableRolesHelperTest extends TestCase
{
    private static $roleHierarchy = array (
        'ROLE_SUPER_ADMIN' => array (
            0 => 'ROLE_ORGANIZATION',
            3 => 'ROLE_SYSTEM',
        ),
        'ROLE_SYSTEM' => array (
            0 => 'ROLE_ADMINUSER',
            1 => 'ROLE_ADMINGROUP',
        ),
        'ROLE_ORGANIZATION' => array (
            0 => 'ROLE_ORGANIZATION_LIST',
            1 => 'ROLE_ORGANIZATION_SEARCH',
            2 => 'ROLE_ORGANIZATION_NEW',
            3 => 'ROLE_ORGANIZATION_EDIT',
            4 => 'ROLE_ORGANIZATION_SHOW',
        ),
        'ROLE_ADMINUSER' => array (
            0 => 'ROLE_ADMINUSER_LIST',
            1 => 'ROLE_ADMINUSER_SEARCH',
            2 => 'ROLE_ADMINUSER_EDIT',
            3 => 'ROLE_ADMINUSER_SHOW',
            4 => 'ROLE_ADMINUSER_DELETE',
        ),
        'ROLE_ADMINGROUP' => array (
            0 => 'ROLE_ADMINGROUP_LIST',
            1 => 'ROLE_ADMINGROUP_SEARCH',
            2 => 'ROLE_ADMINGROUP_NEW',
            3 => 'ROLE_ADMINGROUP_EDIT',
            4 => 'ROLE_ADMINGROUP_SHOW',
            5 => 'ROLE_ADMINGROUP_DELETE',
        ),
    );

    public function testNoTokenReturnsNoRole()
    {
        $tokenStorage = $this->createMock(TokenStorageInterface::class);

        // We just tell tokenStorage to return a not null value
        $tokenStorage->method('getToken')->will($this->returnValue(null));

        $editableRolesHelper = new EditableRolesHelper($tokenStorage, static::$roleHierarchy);

        $this->assertSame(array(), $editableRolesHelper->getRoles());
    }

    public function testRoleHierarchyIsMappedForChoices()
    {
        $tokenStorage = $this->createMock(TokenStorageInterface::class);

        // We just tell tokenStorage to return a not null value
        $tokenStorage->method('getToken')->will($this->returnValue(array('ROLE_ADMIN')));

        $editableRolesHelper = new EditableRolesHelper($tokenStorage, static::$roleHierarchy);

        $expected = array (
            'ROLE_SUPER_ADMIN' => array (
                'ROLE_ORGANIZATION' => 'ROLE_ORGANIZATION',
                'ROLE_SYSTEM' => 'ROLE_SYSTEM',
            ),
            'ROLE_SYSTEM' => array (
                'ROLE_ADMINUSER' => 'ROLE_ADMINUSER',
                'ROLE_ADMINGROUP' => 'ROLE_ADMINGROUP',
            ),
            'ROLE_ORGANIZATION' => array (
                'ROLE_ORGANIZATION_LIST' => 'ROLE_ORGANIZATION_LIST',
                'ROLE_ORGANIZATION_SEARCH' => 'ROLE_ORGANIZATION_SEARCH',
                'ROLE_ORGANIZATION_NEW' => 'ROLE_ORGANIZATION_NEW',
                'ROLE_ORGANIZATION_EDIT' => 'ROLE_ORGANIZATION_EDIT',
                'ROLE_ORGANIZATION_SHOW' => 'ROLE_ORGANIZATION_SHOW',
            ),
            'ROLE_ADMINUSER' => array (
                'ROLE_ADMINUSER_LIST' => 'ROLE_ADMINUSER_LIST',
                'ROLE_ADMINUSER_SEARCH' => 'ROLE_ADMINUSER_SEARCH',
                'ROLE_ADMINUSER_EDIT' => 'ROLE_ADMINUSER_EDIT',
                'ROLE_ADMINUSER_SHOW' => 'ROLE_ADMINUSER_SHOW',
                'ROLE_ADMINUSER_DELETE' => 'ROLE_ADMINUSER_DELETE',
            ),
            'ROLE_ADMINGROUP' => array (
                'ROLE_ADMINGROUP_LIST' => 'ROLE_ADMINGROUP_LIST',
                'ROLE_ADMINGROUP_SEARCH' => 'ROLE_ADMINGROUP_SEARCH',
                'ROLE_ADMINGROUP_NEW' => 'ROLE_ADMINGROUP_NEW',
                'ROLE_ADMINGROUP_EDIT' => 'ROLE_ADMINGROUP_EDIT',
                'ROLE_ADMINGROUP_SHOW' => 'ROLE_ADMINGROUP_SHOW',
                'ROLE_ADMINGROUP_DELETE' => 'ROLE_ADMINGROUP_DELETE',
            ),
        );

        $this->assertSame($expected, $editableRolesHelper->getRoles());
    }
}
