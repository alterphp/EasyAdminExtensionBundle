<?php

namespace AlterPHP\EasyAdminExtensionBundle\Tests\Controller;

use AlterPHP\EasyAdminExtensionBundle\Tests\Fixtures\AbstractTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class UserRolesTest extends AbstractTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->initClient(['environment' => 'user_roles']);
    }

    private function logIn($roles = ['ROLE_ADMIN'])
    {
        $session = static::$client->getContainer()->get('session');

        // the firewall context defaults to the firewall name
        $firewallContext = 'secured_area';

        $token = new UsernamePasswordToken('admin', null, $firewallContext, $roles);
        $session->set('_security_'.$firewallContext, \serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        static::$client->getCookieJar()->set($cookie);
    }

    public function testAdminIsNotReachableWithoutMinimumRole()
    {
        $this->logIn(['ROLE_CATEGORY_LIST']);

        static::$client->followRedirects();

        $crawler = $this->getBackendPage();

        $this->assertSame(403, static::$client->getResponse()->getStatusCode());

        $this->assertSame(
            'You must be granted one of following role(s) [ROLE_ADMIN] to access admin ! (403 Forbidden)',
            \trim($crawler->filterXPath('//head/title')->text())
        );
    }

    public function testAdminIsReachableWithMinimumRole()
    {
        $this->logIn(['ROLE_ADMIN', 'ROLE_CATEGORY_LIST']);

        static::$client->followRedirects();

        $crawler = $this->getBackendPage();

        $this->assertSame(200, static::$client->getResponse()->getStatusCode());
    }

    public function testMenuIsWellPruned()
    {
        $this->logIn(['ROLE_ADMIN', 'ROLE_CATEGORY_LIST']);

        static::$client->followRedirects();

        $crawler = $this->getBackendPage();

        $this->assertSame(200, static::$client->getResponse()->getStatusCode());

        $menuCrawler = $crawler->filter('body ul.sidebar-menu');

        $this->assertSame(
            1,
            $menuCrawler->filter('li > a:contains("Catalog")')->count()
        );
        $this->assertSame(
            1,
            $menuCrawler->filter('li ul li > a:contains("Categories")')->count()
        );
        $this->assertSame(
            0,
            $menuCrawler->filter('li ul li > a:contains("Products")')->count()
        );
        $this->assertSame(
            0,
            $menuCrawler->filter('li > a:contains("Images")')->count()
        );
        // XXX: Native EasyAdmin do not prune empty menu folders
        $this->assertSame(
            1,
            $menuCrawler->filter('li > a:contains("Sales")')->count()
        );
        $this->assertSame(
            0,
            $menuCrawler->filter('li ul li > a:contains("Purchases")')->count()
        );
        $this->assertSame(
            0,
            $menuCrawler->filter('li ul li > a:contains("Purchases items")')->count()
        );
    }

    public function testEntityActionsAreFilteredOnPrefixedRoles()
    {
        $this->logIn(['ROLE_ADMIN', 'ROLE_CATEGORY_LIST', 'ROLE_CATEGORY_SHOW']);

        static::$client->followRedirects();

        $this->getBackendPage(['entity' => 'Category', 'action' => 'list']);
        $this->assertSame(200, static::$client->getResponse()->getStatusCode());

        // Tests that embeddedList is mapped on list action required roles
        $this->getBackendPage(['entity' => 'Category', 'action' => 'embeddedList']);
        $this->assertSame(200, static::$client->getResponse()->getStatusCode());

        $crawler = $this->getBackendPage(['entity' => 'Category', 'action' => 'edit', 'id' => 1]);
        $this->assertSame(403, static::$client->getResponse()->getStatusCode());
        $this->assertSame(
            'You must be granted one of following role(s) [ROLE_CATEGORY_EDIT] to perform this object action ! (403 Forbidden)',
            \trim($crawler->filterXPath('//head/title')->text())
        );

        $this->getBackendPage(['entity' => 'Category', 'action' => 'show', 'id' => 1]);
        $this->assertSame(200, static::$client->getResponse()->getStatusCode());
    }

    public function testEntityActionsAreFilteredOnSpecificRoles()
    {
        $this->logIn(['ROLE_ADMIN', 'ROLE_PRODUCT_LIST', 'ROLE_TEST_EDIT_PRODUCT']);

        static::$client->followRedirects();

        $this->getBackendPage(['entity' => 'Product', 'action' => 'list']);
        $this->assertSame(200, static::$client->getResponse()->getStatusCode());

        $this->getBackendPage(['entity' => 'Product', 'action' => 'edit', 'id' => 1]);
        $this->assertSame(200, static::$client->getResponse()->getStatusCode());

        $crawler = $this->getBackendPage(['entity' => 'Product', 'action' => 'show', 'id' => 1]);
        $this->assertSame(403, static::$client->getResponse()->getStatusCode());
        $this->assertSame(
            'You must be granted one of following role(s) [ROLE_TEST_SHOW_PRODUCT] to perform this object action ! (403 Forbidden)',
            \trim($crawler->filterXPath('//head/title')->text())
        );
    }

    public function testAdminGroupRolesFormMayDisplay()
    {
        $this->logIn(['ROLE_ADMIN', 'ROLE_ADMINGROUP_EDIT']);

        static::$client->followRedirects();

        $crawler = $this->getBackendPage(['entity' => 'AdminGroup', 'action' => 'edit', 'id' => 1]);

        $this->assertSame(200, static::$client->getResponse()->getStatusCode());
        $this->assertSame(
            25,
            $crawler->filter('form#edit-admingroup-form .field-easyadmin_admin_roles input[type="checkbox"]')->count()
        );
    }

    public function testEntityActionsAreForbiddenOnCaseInsensitiveSpecificRoles()
    {
        $this->logIn(['ROLE_ADMIN']);

        static::$client->followRedirects();

        // Edit
        $crawler = $this->getBackendPage(['entity' => 'Product', 'action' => 'edit', 'id' => 1]);
        $this->assertSame(403, static::$client->getResponse()->getStatusCode());
        $this->assertSame(
            'You must be granted one of following role(s) [ROLE_TEST_EDIT_PRODUCT] to perform this object action ! (403 Forbidden)',
            \trim($crawler->filterXPath('//head/title')->text())
        );
        $crawler = $this->getBackendPage(['entity' => 'Product', 'action' => 'Edit', 'id' => 1]);
        $this->assertSame(403, static::$client->getResponse()->getStatusCode());
        $this->assertSame(
            'You must be granted one of following role(s) [ROLE_TEST_EDIT_PRODUCT] to perform this object action ! (403 Forbidden)',
            \trim($crawler->filterXPath('//head/title')->text())
        );
        $crawler = $this->getBackendPage(['entity' => 'Product', 'action' => 'EDIT', 'id' => 1]);
        $this->assertSame(403, static::$client->getResponse()->getStatusCode());
        $this->assertSame(
            'You must be granted one of following role(s) [ROLE_TEST_EDIT_PRODUCT] to perform this object action ! (403 Forbidden)',
            \trim($crawler->filterXPath('//head/title')->text())
        );

        // Show
        $crawler = $this->getBackendPage(['entity' => 'Product', 'action' => 'show', 'id' => 1]);
        $this->assertSame(403, static::$client->getResponse()->getStatusCode());
        $this->assertSame(
            'You must be granted one of following role(s) [ROLE_TEST_SHOW_PRODUCT] to perform this object action ! (403 Forbidden)',
            \trim($crawler->filterXPath('//head/title')->text())
        );
        $crawler = $this->getBackendPage(['entity' => 'Product', 'action' => 'Show', 'id' => 1]);
        $this->assertSame(403, static::$client->getResponse()->getStatusCode());
        $this->assertSame(
            'You must be granted one of following role(s) [ROLE_TEST_SHOW_PRODUCT] to perform this object action ! (403 Forbidden)',
            \trim($crawler->filterXPath('//head/title')->text())
        );
        $crawler = $this->getBackendPage(['entity' => 'Product', 'action' => 'SHOW', 'id' => 1]);
        $this->assertSame(403, static::$client->getResponse()->getStatusCode());
        $this->assertSame(
            'You must be granted one of following role(s) [ROLE_TEST_SHOW_PRODUCT] to perform this object action ! (403 Forbidden)',
            \trim($crawler->filterXPath('//head/title')->text())
        );
    }
}
