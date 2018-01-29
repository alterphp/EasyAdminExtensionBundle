<?php

namespace AlterPHP\EasyAdminExtensionBundle\Tests\Helper;

use AlterPHP\EasyAdminExtensionBundle\Model\AdminGroup;
use AlterPHP\EasyAdminExtensionBundle\Model\AdminUser;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

class AdminUserTest extends TestCase
{
    public function testAdminUserGetRolesFromGroups()
    {
        $aGroup = new AdminGroup();
        $aGroup->setRoles(array('ROLE_COMMON', 'ROLE_A_1', 'ROLE_A_2'));

        $bGroup = new AdminGroup();
        $bGroup->setRoles(array('ROLE_COMMON', 'ROLE_B_1', 'ROLE_B_2'));

        $user = $this->createPartialMock(AdminUser::class, array('getGroups'));
        $user->method('getGroups')->will($this->returnValue(new ArrayCollection([$aGroup, $bGroup])));

        $this->assertSame(
            array('ROLE_ADMIN', 'ROLE_COMMON', 'ROLE_A_1', 'ROLE_A_2', 'ROLE_B_1', 'ROLE_B_2'),
            $user->getRoles()
        );
    }
}
