<?php

namespace AlterPHP\EasyAdminExtensionBundle\Tests\Controller;

use AlterPHP\EasyAdminExtensionBundle\Tests\Fixtures\AbstractTestCase;

class EmbeddedListTest extends AbstractTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->initClient(array('environment' => 'embedded_list'));
    }

    public function testEmbeddedListisDisplaid()
    {
        $crawler = $this->requestEditView('Category', 1);

        $forAttrValue = '/admin/?entity=Product&action=embeddedList&filters%5Bentity.category%5D=1';
        $this->assertSame(1, $crawler->filter('.embedded-list[for="'.$forAttrValue.'"]')->count());
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

    public function testRequestNullFilterIsApplied()
    {
        $crawler = $this->requestListView(
            'Product', array('entity.phone' => '_NULL')
        );

        $this->assertSame(10, $crawler->filter('#main tr[data-id]')->count());
    }

    public function testRequestNotNullFilterIsApplied()
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
