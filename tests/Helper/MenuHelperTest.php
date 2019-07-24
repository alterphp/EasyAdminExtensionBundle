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
        $menuConfig = [
            0 => ['label' => 'Dashboard', 'type' => 'route', 'children' => []],
            1 => ['label' => 'Organizations', 'type' => 'entity', 'entity' => 'Organization'],
            2 => ['label' => 'Members', 'type' => 'entity', 'entity' => 'Member'],
            3 => ['label' => 'Events', 'type' => 'empty', 'children' => [
                0 => ['label' => 'Seminaries', 'type' => 'entity', 'entity' => 'Seminary'],
                1 => ['label' => 'Meetings', 'type' => 'entity', 'entity' => 'Meeting'],
                2 => ['label' => 'Plenary meetings', 'type' => 'entity', 'entity' => 'PlenaryMeeting'],
            ]],
            4 => ['label' => 'System', 'type' => 'empty', 'children' => [
                0 => ['label' => 'Admin users', 'type' => 'entity', 'entity' => 'AdminUser'],
                1 => ['label' => 'Admin groups', 'type' => 'entity', 'entity' => 'AdminGroup'],
            ]],
        ];

        $entitiesConfig = [
            'Organization' => ['role_prefix' => 'ROLE_ORGANIZATION'],
            'Member' => ['role_prefix' => 'ROLE_MEMBER'],
            'Seminary' => ['role_prefix' => 'ROLE_SEMINARY'],
            'Meeting' => ['role_prefix' => 'ROLE_MEETING'],
            'PlenaryMeeting' => ['role_prefix' => 'ROLE_PLENARYMEETING'],
            'AdminUser' => ['role_prefix' => 'ROLE_ADMINUSER'],
            'AdminGroup' => ['role_prefix' => 'ROLE_ADMINGROUP'],
        ];

        $adminAuthorizationChecker = $this->createMock(AdminAuthorizationChecker::class);
        $symfonyAuthorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);

        $grantedRoleMap = [
            [$entitiesConfig['Organization'], 'list', null, true],
            [$entitiesConfig['Member'], 'list', null, false],
            [$entitiesConfig['Seminary'], 'list', null, true],
            [$entitiesConfig['Meeting'], 'list', null, false],
            [$entitiesConfig['PlenaryMeeting'], 'list', null, true],
            [$entitiesConfig['AdminUser'], 'list', null, false],
            [$entitiesConfig['AdminGroup'], 'list', null, false],
        ];
        $adminAuthorizationChecker->method('isEasyAdminGranted')->will($this->returnValueMap($grantedRoleMap));

        $helper = new MenuHelper($adminAuthorizationChecker, $symfonyAuthorizationChecker);

        $prunedMenu = $helper->pruneMenuItems($menuConfig, $entitiesConfig);

        $expectedPrunedMenu = [
            0 => [
                'label' => 'Dashboard',
                'type' => 'route',
                'children' => [],
                'menu_index' => 0,
                'submenu_index' => -1,
            ],
            1 => [
                'label' => 'Organizations',
                'type' => 'entity',
                'entity' => 'Organization',
                'menu_index' => 1,
                'submenu_index' => -1,
            ],
            2 => [
                'label' => 'Events',
                'type' => 'empty',
                'children' => [
                    0 => [
                        'label' => 'Seminaries',
                        'type' => 'entity',
                        'entity' => 'Seminary',
                        'menu_index' => 2,
                        'submenu_index' => 0,
                    ],
                    1 => [
                        'label' => 'Plenary meetings',
                        'type' => 'entity',
                        'entity' => 'PlenaryMeeting',
                        'menu_index' => 2,
                        'submenu_index' => 1,
                    ],
                ],
                'menu_index' => 2,
                'submenu_index' => -1,
            ],
        ];

        $this->assertSame($expectedPrunedMenu, $prunedMenu);
    }

    public function testAcessDeniedStaticEntriesArePruned()
    {
        $menuConfig = [
            0 => ['label' => 'Link allowed', 'type' => 'link', 'url' => 'https://github.com', 'role' => 'ROLE_ALLOWED'],
            1 => ['label' => 'Link disallowed', 'type' => 'link', 'url' => 'https://gitlab.com', 'role' => 'ROLE_DISALLOWED'],
            2 => ['label' => 'Route allowed', 'type' => 'route', 'route' => 'route_allowed', 'role' => 'ROLE_ALLOWED'],
            3 => ['label' => 'Route disallowed', 'type' => 'route', 'route' => 'route_disallowed', 'role' => 'ROLE_DISALLOWED'],
        ];

        $adminAuthorizationChecker = $this->createMock(AdminAuthorizationChecker::class);
        $symfonyAuthorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);

        $grantedRoleMap = [
            [$menuConfig[0]['role'], null, true],
            [$menuConfig[1]['role'], null, false],
            [$menuConfig[2]['role'], null, true],
            [$menuConfig[3]['role'], null, false],
        ];
        $symfonyAuthorizationChecker->method('isGranted')->will($this->returnValueMap($grantedRoleMap));

        $helper = new MenuHelper($adminAuthorizationChecker, $symfonyAuthorizationChecker);

        $prunedMenu = $helper->pruneMenuItems($menuConfig, []);

        $expectedPrunedMenu = [
            0 => [
                'label' => 'Link allowed',
                'type' => 'link',
                'url' => 'https://github.com',
                'role' => 'ROLE_ALLOWED',
                'menu_index' => 0,
                'submenu_index' => -1,
            ],
            1 => [
                'label' => 'Route allowed',
                'type' => 'route',
                'route' => 'route_allowed',
                'role' => 'ROLE_ALLOWED',
                'menu_index' => 1,
                'submenu_index' => -1,
            ],
        ];

        $this->assertSame($expectedPrunedMenu, $prunedMenu);
    }
}
