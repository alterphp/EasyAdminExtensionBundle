<?php

namespace AlterPHP\EasyAdminExtensionBundle\Tests\Controller;

use AlterPHP\EasyAdminExtensionBundle\Tests\Fixtures\AbstractTestCase;

class ListFormFiltersTest extends AbstractTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->initClient(array('environment' => 'list_form_filters'));
    }

    public function testListFiltersAreDisplaid()
    {
        $crawler = $this->requestListView('Product');

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $listFormFiltersCrawler = $crawler->filter('#list-form-filters');

        $this->assertSame(1, $listFormFiltersCrawler->filter('select#form_filters_oddEven[multiple]')->count());
        $this->assertSame(1, $listFormFiltersCrawler->filter('select#form_filters_category_autocomplete[multiple]')->count());
        $this->assertSame(1, $listFormFiltersCrawler->filter('select#form_filters_replenishmentType[multiple]')->count());
        $this->assertSame(1, $listFormFiltersCrawler->filter('select#form_filters_enabled')->count());
    }

    public function testFormSingleFilterIsApplied()
    {
        $crawler = $this->requestListView('Product', array(), array('enabled' => false));

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertSame(10, $crawler->filter('#main tr[data-id]')->count());
    }

    public function testFormSingleEasyadminAutocompleteFilterIsApplied()
    {
        $crawler = $this->requestListView('Product', array(), array('category' => array('autocomplete' => array(1))));

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertSame(10, $crawler->filter('#main tr[data-id]')->count());
    }
}
