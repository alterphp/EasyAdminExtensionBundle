<?php

namespace AlterPHP\EasyAdminExtensionBundle\Tests\Controller;

use AlterPHP\EasyAdminExtensionBundle\Tests\Fixtures\AbstractTestCase;

class RequestParametersTest extends AbstractTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->initClient(['environment' => 'default_backend']);
    }

    public function testRequestSingleFilterIsApplied()
    {
        $crawler = $this->requestListView('Product', ['entity.enabled' => false]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertSame(10, $crawler->filter('#main tr[data-id]')->count());
    }

    public function testRequestNoFieldFilterCausesNoError()
    {
        $crawler = $this->requestListView('Product', ['entity.foo' => 'bar']);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertSame(15, $crawler->filter('#main tr[data-id]')->count());
    }

    public function testRequestManyFiltersAreApplied()
    {
        $crawler = $this->requestListView(
            'Product', ['entity.enabled' => false, 'entity.oddEven' => 'even']
        );

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertSame(5, $crawler->filter('#main tr[data-id]')->count());
    }

    public function testRequestFilterWithoutAliasIsCompletedAndApplied()
    {
        $crawler = $this->requestListView('Product', ['enabled' => false]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertSame(10, $crawler->filter('#main tr[data-id]')->count());
    }

    public function testRequestFiltersArePassedToSearchForm()
    {
        $crawler = $this->requestListView(
            'Product', ['entity.enabled' => false, 'entity.oddEven' => 'even']
        );

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $searchFormCrawler = $crawler->filter('.action-search form');

        $this->assertSame(
            1,
            $searchFormCrawler->filter('input[name="filters[entity.enabled]"][value="0"]')->count()
        );
        $this->assertSame(
            1,
            $searchFormCrawler->filter('input[name="filters[entity.oddEven]"][value="even"]')->count()
        );
    }

    public function testRequestMultivalueFiltersAreApplied()
    {
        $crawler = $this->requestListView(
            'Product', ['entity.oddEven' => ['odd', 'even']]
        );

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertContains(
            '1 - 15 of 100',
            $crawler->filter('section.content-footer .list-pagination')->text()
        );

        $searchFormCrawler = $crawler->filter('.action-search form');

        $this->assertSame(
            1,
            $searchFormCrawler->filter('input[name="filters[entity.oddEven][]"][value="odd"]')->count()
        );
        $this->assertSame(
            1,
            $searchFormCrawler->filter('input[name="filters[entity.oddEven][]"][value="even"]')->count()
        );
    }

    public function testRequestFilterIsAppliedToSearchAction()
    {
        $crawler = $this->requestSearchView('ref000', 'Product', ['entity.enabled' => false]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertSame(10, $crawler->filter('#main tr[data-id]')->count());
    }

    public function testRequestNullFilterIsApplied()
    {
        $crawler = $this->requestListView(
            'Product', ['entity.phone' => '_NULL']
        );

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertSame(10, $crawler->filter('#main tr[data-id]')->count());
    }

    public function testRequestNotNullFilterIsApplied()
    {
        $crawler = $this->requestListView(
            'Product', ['entity.phone' => '_NOT_NULL']
        );

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertContains(
            '1 - 15 of 90',
            $crawler->filter('section.content-footer .list-pagination')->text()
        );
    }

    public function testRequestEmptyFilterIsIgnored()
    {
        $crawler = $this->requestListView(
            'Product', ['entity.phone' => '']
        );

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertContains(
            '1 - 15 of 100',
            $crawler->filter('section.content-footer .list-pagination')->text()
        );
    }
}
