<?php

namespace AlterPHP\EasyAdminExtensionBundle\Tests\Controller;

use AlterPHP\EasyAdminExtensionBundle\Tests\Fixtures\AbstractTestCase;

class ListFiltersTest extends AbstractTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->initClient(array('environment' => 'list_filters'));
    }

    public function testListFiltersAreDisplaid()
    {
        $crawler = $this->requestListView('Product');

        $listFiltersCrawler = $crawler->filter('#list-filters');

        $this->assertSame(1, $listFiltersCrawler->filter('select#list_filters_oddEven[multiple]')->count());
        $this->assertSame(1, $listFiltersCrawler->filter('select#list_filters_category_autocomplete[multiple]')->count());
        $this->assertSame(1, $listFiltersCrawler->filter('select#list_filters_replenishmentType[multiple]')->count());
        $this->assertSame(1, $listFiltersCrawler->filter('select#list_filters_enabled')->count());
    }
}
