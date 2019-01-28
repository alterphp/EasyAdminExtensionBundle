<?php

namespace AlterPHP\EasyAdminExtensionBundle\Tests\Controller;

use AlterPHP\EasyAdminExtensionBundle\Tests\Fixtures\AbstractTestCase;

class ListFormFiltersTest extends AbstractTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->initClient(['environment' => 'list_form_filters']);
    }

    public function testListFiltersAreDisplaid()
    {
        $crawler = $this->requestListView('Product');

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $listFormFiltersCrawler = $crawler->filter('#list-form-filters');

        $this->assertSame(1, $listFormFiltersCrawler->filter('select#form_filters_oddEven_value[multiple]')->count());
        $this->assertSame(1, $listFormFiltersCrawler->filter('select#form_filters_category_value_autocomplete[multiple]')->count());
        $this->assertSame(1, $listFormFiltersCrawler->filter('select#form_filters_replenishmentType_value[multiple]')->count());
        $this->assertSame(1, $listFormFiltersCrawler->filter('select#form_filters_enabled_value')->count());
    }

    public function testFormSingleFilterIsApplied()
    {
        $crawler = $this->requestListView('Product', [], ['enabled' => ['value' => false]]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertContains(
            '10 results',
            $crawler->filter('section.content-footer .list-pagination-counter')->text()
        );
    }

    public function testFormSingleEasyadminAutocompleteFilterIsApplied()
    {
        $crawler = $this->requestListView('Product', [], ['category' => ['value' => ['autocomplete' => [1]]]]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertContains(
            '10 results',
            $crawler->filter('section.content-footer .list-pagination-counter')->text()
        );
    }

    public function testListFilterGreaterThanOperator()
    {
        $crawler = $this->requestListView('Product', [], ['priceGreaterThan' => ['value' => 5100]]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertContains(
            '49 results',
            $crawler->filter('section.content-footer .list-pagination-counter')->text()
        );
    }

    public function testListFilterGreaterThanOrEqualsOperator()
    {
        $crawler = $this->requestListView('Product', [], ['priceGreaterThanOrEquals' => ['value' => 5100]]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertContains(
            '50 results',
            $crawler->filter('section.content-footer .list-pagination-counter')->text()
        );
    }

    public function testListFilterLowerThanOperator()
    {
        $crawler = $this->requestListView('Product', [], ['priceLowerThan' => ['value' => 5100]]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertContains(
            '50 results',
            $crawler->filter('section.content-footer .list-pagination-counter')->text()
        );
    }

    public function testListFilterLowerThanOrEqualsOperator()
    {
        $crawler = $this->requestListView('Product', [], ['priceLowerThanOrEquals' => ['value' => 5100]]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertContains(
            '51 results',
            $crawler->filter('section.content-footer .list-pagination-counter')->text()
        );
    }

    public function testListFilterNotOperator()
    {
        $crawler = $this->requestListView('Product', [], ['notOddEven' => ['value' => 'even']]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertContains(
            '75 results',
            $crawler->filter('section.content-footer .list-pagination-counter')->text()
        );
    }

    public function testListFilterNotInOperator()
    {
        $crawler = $this->requestListView(
            'Product',
            [],
            ['notInPhone' => ['value' => ['0123456789-0', '0123456789-1', '0123456789-2', '0123456789-3']]]
        );

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertContains(
            '54 results',
            $crawler->filter('section.content-footer .list-pagination-counter')->text()
        );
    }

    public function testListFilterCombinedOperator()
    {
        $crawler = $this->requestListView(
            'Product',
            [],
            ['priceLowerThanOrEquals' => ['value' => 5100], 'priceGreaterThan' => ['value' => 3000]]
        );

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertContains(
            '21 results',
            $crawler->filter('section.content-footer .list-pagination-counter')->text()
        );
    }
}
