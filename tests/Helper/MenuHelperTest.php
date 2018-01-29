<?php

namespace AlterPHP\EasyAdminExtensionBundle\Tests\Helper;

use AlterPHP\EasyAdminExtensionBundle\Helper\MenuHelper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class MenuHelperTest extends TestCase
{
    private $menuConfig = array(
        0 => array('label' => 'Dashboard', 'type' => 'route', 'children' => array()),
        1 => array('label' => 'Organizations', 'type' => 'entity', 'role' => 'ROLE_ORGANIZATION_LIST', 'children' => array()),
        2 => array('label' => 'Members', 'type' => 'entity', 'role' => 'ROLE_PLAYER_LIST', 'children' => array()),
        3 => array('label' => 'Events', 'type' => 'empty', 'children' => array(
            0 => array('label' => 'Seminaries', 'type' => 'entity', 'role' => 'ROLE_SEMINARY_LIST'),
            1 => array('label' => 'Meetings', 'type' => 'entity', 'role' => 'ROLE_MEETING_LIST'),
            2 => array('label' => 'Plenary meetings', 'type' => 'entity', 'role' => 'ROLE_PLENARYMEETING_LIST'),
        )),
        4 => array('label' => 'System', 'type' => 'empty', 'children' => array(
            0 => array('label' => 'Admin users', 'type' => 'entity', 'role' => 'ROLE_ADMINUSER_LIST'),
            1 => array('label' => 'Admin groups', 'type' => 'entity', 'role' => 'ROLE_ADMINGROUP_LIST'),
        )),
    );

    public function testAcessDeniedEntriesArePruned()
    {
        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);

        // We just tell tokenStorage to return a not null value
        $tokenStorage->method('getToken')->will($this->returnValue('foo'));

        $grantedRoleMap = array(
            array('ROLE_ORGANIZATION_LIST', null, true),
            array('ROLE_PLAYER_LIST', null, false),
            array('ROLE_SEMINARY_LIST', null, true),
            array('ROLE_MEETING_LIST', null, false),
            array('ROLE_PLENARYMEETING_LIST', null, true),
            array('ROLE_ADMINUSER_LIST', null, false),
            array('ROLE_ADMINGROUP_LIST', null, false),
        );
        $authorizationChecker->method('isGranted')->will($this->returnValueMap($grantedRoleMap));

        $helper = new MenuHelper($tokenStorage, $authorizationChecker);

        $prunedMenu = $helper->pruneMenuItems($this->menuConfig);

        $expectedPrunedMenu = array(
            0 => array(
                "label" => "Dashboard",
                "type" => "route",
                "children" => array(),
            ),
            1 => array(
                "label" => "Organizations",
                "type" => "entity",
                "role" => "ROLE_ORGANIZATION_LIST",
                "children" => array(),
            ),
            3 => array(
                "label" => "Events",
                "type" => "empty",
                "children" => array(
                    0 => array(
                        "label" => "Seminaries",
                        "type" => "entity",
                        "role" => "ROLE_SEMINARY_LIST",
                    ),
                    2 => array(
                        "label" => "Plenary meetings",
                        "type" => "entity",
                        "role" => "ROLE_PLENARYMEETING_LIST",
                    ),
                ),
            ),
        );

        $this->assertSame($expectedPrunedMenu, $prunedMenu);
    }

    public function testRestrictedEntriesArePrunedIfNoToken()
    {
        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);

        // We just tell tokenStorage to return a not null value
        $tokenStorage->method('getToken')->will($this->returnValue(null));

        $grantedRoleMap = array(
            array('ROLE_ORGANIZATION_LIST', null, true),
            array('ROLE_PLAYER_LIST', null, false),
            array('ROLE_SEMINARY_LIST', null, true),
            array('ROLE_MEETING_LIST', null, false),
            array('ROLE_PLENARYMEETING_LIST', null, true),
            array('ROLE_ADMINUSER_LIST', null, false),
            array('ROLE_ADMINGROUP_LIST', null, false),
        );
        $authorizationChecker->method('isGranted')->will($this->returnValueMap($grantedRoleMap));

        $helper = new MenuHelper($tokenStorage, $authorizationChecker);

        $prunedMenu = $helper->pruneMenuItems($this->menuConfig);

        $expectedPrunedMenu = array(
            0 => array(
                "label" => "Dashboard",
                "type" => "route",
                "children" => array(),
            )
        );

        $this->assertSame($expectedPrunedMenu, $prunedMenu);
    }
}
