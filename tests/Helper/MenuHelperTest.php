<?php

namespace AlterPHP\EasyAdminExtensionBundle\Tests\Helper;

use AlterPHP\EasyAdminExtensionBundle\Helper\MenuHelper;
use AlterPHP\EasyAdminExtensionBundle\Security\AdminAuthorizationChecker;
use PHPUnit\Framework\TestCase;

class MenuHelperTest extends TestCase
{
    private $menuConfig = array(
        0 => array('label' => 'Dashboard', 'type' => 'route', 'children' => array()),
        1 => array('label' => 'Organizations', 'type' => 'entity', 'entity' => 'Organization'),
        2 => array('label' => 'Members', 'type' => 'entity', 'entity' => 'Member'),
        3 => array('label' => 'Events', 'type' => 'empty', 'children' => array(
            0 => array('label' => 'Seminaries', 'type' => 'entity', 'entity' => 'Seminary'),
            1 => array('label' => 'Meetings', 'type' => 'entity', 'entity' => 'Meeting'),
            2 => array('label' => 'Plenary meetings', 'type' => 'entity', 'entity' => 'PlenaryMeeting'),
        )),
        4 => array('label' => 'System', 'type' => 'empty', 'children' => array(
            0 => array('label' => 'Admin users', 'type' => 'entity', 'entity' => 'AdminUser'),
            1 => array('label' => 'Admin groups', 'type' => 'entity', 'entity' => 'AdminGroup'),
        )),
    );

    private $entitiesConfig = array(
        'Organization' => array('role_prefix' => 'ROLE_ORGANIZATION'),
        'Member' => array('role_prefix' => 'ROLE_MEMBER'),
        'Seminary' => array('role_prefix' => 'ROLE_SEMINARY'),
        'Meeting' => array('role_prefix' => 'ROLE_MEETING'),
        'PlenaryMeeting' => array('role_prefix' => 'ROLE_PLENARYMEETING'),
        'AdminUser' => array('role_prefix' => 'ROLE_ADMINUSER'),
        'AdminGroup' => array('role_prefix' => 'ROLE_ADMINGROUP'),
    );

    public function testAcessDeniedEntriesArePruned()
    {
        $adminAuthorizationChecker = $this->createMock(AdminAuthorizationChecker::class);

        $grantedRoleMap = array(
            array($this->entitiesConfig['Organization'], 'list', true),
            array($this->entitiesConfig['Member'], 'list', false),
            array($this->entitiesConfig['Seminary'], 'list', true),
            array($this->entitiesConfig['Meeting'], 'list', false),
            array($this->entitiesConfig['PlenaryMeeting'], 'list', true),
            array($this->entitiesConfig['AdminUser'], 'list', false),
            array($this->entitiesConfig['AdminGroup'], 'list', false),
        );
        $adminAuthorizationChecker->method('isEasyAdminGranted')->will($this->returnValueMap($grantedRoleMap));

        $helper = new MenuHelper($adminAuthorizationChecker);

        $prunedMenu = $helper->pruneMenuItems($this->menuConfig, $this->entitiesConfig);

        $expectedPrunedMenu = array(
            0 => array(
                'label' => 'Dashboard',
                'type' => 'route',
                'children' => array(),
            ),
            1 => array(
                'label' => 'Organizations',
                'type' => 'entity',
                'entity' => 'Organization',
            ),
            3 => array(
                'label' => 'Events',
                'type' => 'empty',
                'children' => array(
                    0 => array(
                        'label' => 'Seminaries',
                        'type' => 'entity',
                        'entity' => 'Seminary',
                    ),
                    2 => array(
                        'label' => 'Plenary meetings',
                        'type' => 'entity',
                        'entity' => 'PlenaryMeeting',
                    ),
                ),
            ),
        );

        $this->assertSame($expectedPrunedMenu, $prunedMenu);
    }
}
