<?php

namespace AlterPHP\EasyAdminExtensionBundle\Tests\Controller;

use AlterPHP\EasyAdminExtensionBundle\Tests\Fixtures\AbstractTestCase;

class RequestParametersTest extends AbstractTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->initClient(array('environment' => 'default_backend'));
    }

    public function testRequestSingleFilterIsApplied()
    {
        $crawler = $this->requestListView('Product', array('entity.enabled' => false));

        $this->assertSame(10, $crawler->filter('#main tr[data-id]')->count());
    }

    public function testRequestNoFieldFilterCausesNoError()
    {
        $crawler = $this->requestListView('Product', array('entity.foo' => 'bar'));

        $this->assertSame(15, $crawler->filter('#main tr[data-id]')->count());
    }

    public function testRequestManyFiltersAreApplied()
    {
        $crawler = $this->requestListView(
            'Product', array('entity.enabled' => false, 'entity.oddEven' => 'even')
        );

        $this->assertSame(5, $crawler->filter('#main tr[data-id]')->count());
    }

    public function testRequestFilterWithoutAliasIsCompletedAndApplied()
    {
        $crawler = $this->requestListView('Product', array('enabled' => false));

        $this->assertSame(10, $crawler->filter('#main tr[data-id]')->count());
    }

    public function testRequestFiltersArePassedToSearchForm()
    {
        $crawler = $this->requestListView(
            'Product', array('entity.enabled' => false, 'entity.oddEven' => 'even')
        );

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
            'Product', array('entity.oddEven' => array('odd', 'even'))
        );

        $this->assertContains(
            '1 - 15 of 100',
            $crawler->filter('#main .list-pagination')->text()
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
        $crawler = $this->requestSearchView('ref000', 'Product', array('entity.enabled' => false));

        $this->assertSame(10, $crawler->filter('#main tr[data-id]')->count());
    }

    public function testRequestNullFilterisApplied()
    {
        $crawler = $this->requestListView(
            'Product', array('entity.phone' => '_NULL')
        );

        $this->assertSame(10, $crawler->filter('#main tr[data-id]')->count());
    }

    public function testRequestNotNullFilterisApplied()
    {
        $crawler = $this->requestListView(
            'Product', array('entity.phone' => '_NOT_NULL')
        );
        $this->assertContains(
            '1 - 15 of 90',
            $crawler->filter('#main .list-pagination')->text()
        );
    }
}
