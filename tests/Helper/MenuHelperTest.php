<?php

namespace AlterPHP\EasyAdminExtensionBundle\Tests\Helper;

use AlterPHP\EasyAdminExtensionBundle\Helper\MenuHelper;
use AlterPHP\EasyAdminExtensionBundle\Security\AdminAuthorizationChecker;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class MenuHelperTest extends TestCase
{

    public function testAcessDeniedEntityEntriesArePruned()
    {
        $menuConfig = array(
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

        $entitiesConfig = array(
            'Organization' => array('role_prefix' => 'ROLE_ORGANIZATION'),
            'Member' => array('role_prefix' => 'ROLE_MEMBER'),
            'Seminary' => array('role_prefix' => 'ROLE_SEMINARY'),
            'Meeting' => array('role_prefix' => 'ROLE_MEETING'),
            'PlenaryMeeting' => array('role_prefix' => 'ROLE_PLENARYMEETING'),
            'AdminUser' => array('role_prefix' => 'ROLE_ADMINUSER'),
            'AdminGroup' => array('role_prefix' => 'ROLE_ADMINGROUP'),
        );

        $adminAuthorizationChecker = $this->createMock(AdminAuthorizationChecker::class);
        $symfonyAuthorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);

        $grantedRoleMap = array(
            array($entitiesConfig['Organization'], 'list', null, true),
            array($entitiesConfig['Member'], 'list', null, false),
            array($entitiesConfig['Seminary'], 'list', null, true),
            array($entitiesConfig['Meeting'], 'list', null, false),
            array($entitiesConfig['PlenaryMeeting'], 'list', null, true),
            array($entitiesConfig['AdminUser'], 'list', null, false),
            array($entitiesConfig['AdminGroup'], 'list', null, false),
        );
        $adminAuthorizationChecker->method('isEasyAdminGranted')->will($this->returnValueMap($grantedRoleMap));

        $helper = new MenuHelper($adminAuthorizationChecker, $symfonyAuthorizationChecker);

        $prunedMenu = $helper->pruneMenuItems($menuConfig, $entitiesConfig);

        $expectedPrunedMenu = array(
            0 => array(
                'label' => 'Dashboard',
                'type' => 'route',
                'children' => array(),
                'menu_index' => 0,
                'submenu_index' => -1,
            ),
            1 => array(
                'label' => 'Organizations',
                'type' => 'entity',
                'entity' => 'Organization',
                'menu_index' => 1,
                'submenu_index' => -1,
            ),
            2 => array(
                'label' => 'Events',
                'type' => 'empty',
                'children' => array(
                    0 => array(
                        'label' => 'Seminaries',
                        'type' => 'entity',
                        'entity' => 'Seminary',
                        'menu_index' => 2,
                        'submenu_index' => 0,
                    ),
                    1 => array(
                        'label' => 'Plenary meetings',
                        'type' => 'entity',
                        'entity' => 'PlenaryMeeting',
                        'menu_index' => 2,
                        'submenu_index' => 1,
                    ),
                ),
                'menu_index' => 2,
                'submenu_index' => -1,
            ),
        );

        $this->assertSame($expectedPrunedMenu, $prunedMenu);
    }

    public function testAcessDeniedStaticEntriesArePruned()
    {
        $menuConfig = array(
            0 => array('label' => 'Link allowed', 'type' => 'link', 'url' => 'https://github.com', 'role' => 'ROLE_ALLOWED'),
            1 => array('label' => 'Link disallowed', 'type' => 'link', 'url' => 'https://gitlab.com', 'role' => 'ROLE_DISALLOWED'),
            2 => array('label' => 'Route allowed', 'type' => 'route', 'route' => 'route_allowed', 'role' => 'ROLE_ALLOWED'),
            3 => array('label' => 'Route disallowed', 'type' => 'route', 'route' => 'route_disallowed', 'role' => 'ROLE_DISALLOWED'),
        );

        $adminAuthorizationChecker = $this->createMock(AdminAuthorizationChecker::class);
        $symfonyAuthorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);

        $grantedRoleMap = array(
            array($menuConfig[0]['role'], null, true),
            array($menuConfig[1]['role'], null, false),
            array($menuConfig[2]['role'], null, true),
            array($menuConfig[3]['role'], null, false),
        );
        $symfonyAuthorizationChecker->method('isGranted')->will($this->returnValueMap($grantedRoleMap));

        $helper = new MenuHelper($adminAuthorizationChecker, $symfonyAuthorizationChecker);

        $prunedMenu = $helper->pruneMenuItems($menuConfig, array());

        $expectedPrunedMenu = array(
            0 => array(
                'label' => 'Link allowed',
                'type' => 'link',
                'url' => 'https://github.com',
                'role' => 'ROLE_ALLOWED',
                'menu_index' => 0,
                'submenu_index' => -1,
            ),
            1 => array(
                'label' => 'Route allowed',
                'type' => 'route',
                'route' => 'route_allowed',
                'role' => 'ROLE_ALLOWED',
                'menu_index' => 1,
                'submenu_index' => -1,
            ),
        );

        $this->assertSame($expectedPrunedMenu, $prunedMenu);
    }
}
