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

    public function testManyToManyEmbedddLists()
    {
        $crawler = $this->requestEditView('AdminGroup', 1);

        $forAttrValue = md5('/admin/?entity=AdminUser&action=embeddedList&filters%5Bentity.id%5D%5B0%5D=1&filters%5Bentity.id%5D%5B1%5D=2&filters%5Bentity.id%5D%5B2%5D=3&filters%5Bentity.id%5D%5B3%5D=4&filters%5Bentity.id%5D%5B4%5D=5&filters%5Bentity.id%5D%5B5%5D=6&filters%5Bentity.id%5D%5B6%5D=7&filters%5Bentity.id%5D%5B7%5D=8&filters%5Bentity.id%5D%5B8%5D=9&filters%5Bentity.id%5D%5B9%5D=10&filters%5Bentity.id%5D%5B10%5D=11&filters%5Bentity.id%5D%5B11%5D=12&filters%5Bentity.id%5D%5B12%5D=13&filters%5Bentity.id%5D%5B13%5D=14&filters%5Bentity.id%5D%5B14%5D=15&filters%5Bentity.id%5D%5B15%5D=16&filters%5Bentity.id%5D%5B16%5D=17&filters%5Bentity.id%5D%5B17%5D=18&filters%5Bentity.id%5D%5B18%5D=19&filters%5Bentity.id%5D%5B19%5D=20');

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertSame(1, $crawler->filter('.embedded-list[for="'.$forAttrValue.'"]')->count());
    }

    public function testEmbeddedListIsDisplaidInEdit()
    {
        $crawler = $this->requestEditView('Category', 1);

        $forAttrValue = md5('/admin/?entity=Product&action=embeddedList&filters%5Bentity.category%5D=1');

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertSame(1, $crawler->filter('.embedded-list[for="'.$forAttrValue.'"]')->count());
    }

    public function testEmbeddedListIsDisplaidInShow()
    {
        $crawler = $this->requestShowView('Category', 1);

        $forAttrValue = md5('/admin/?entity=Product&action=embeddedList&filters%5Bentity.category%5D=1');

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertSame(1, $crawler->filter('.embedded-list[for="'.$forAttrValue.'"]')->count());
    }

    public function testRequestSingleFilterIsApplied()
    {
        $crawler = $this->requestListView('Product', array('entity.enabled' => false));

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertSame(10, $crawler->filter('#main tr[data-id]')->count());
    }

    public function testRequestNoFieldFilterCausesNoError()
    {
        $crawler = $this->requestListView('Product', array('entity.foo' => 'bar'));

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertSame(15, $crawler->filter('#main tr[data-id]')->count());
    }

    public function testRequestManyFiltersAreApplied()
    {
        $crawler = $this->requestListView(
            'Product', array('entity.enabled' => false, 'entity.oddEven' => 'even')
        );

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertSame(5, $crawler->filter('#main tr[data-id]')->count());
    }

    public function testRequestFilterWithoutAliasIsCompletedAndApplied()
    {
        $crawler = $this->requestListView('Product', array('enabled' => false));

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertSame(10, $crawler->filter('#main tr[data-id]')->count());
    }

    public function testRequestFiltersArePassedToSearchForm()
    {
        $crawler = $this->requestListView(
            'Product', array('entity.enabled' => false, 'entity.oddEven' => 'even')
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
            'Product', array('entity.oddEven' => array('odd', 'even'))
        );

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
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

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertSame(10, $crawler->filter('#main tr[data-id]')->count());
    }

    public function testRequestNotNullFilterIsApplied()
    {
        $crawler = $this->requestListView(
            'Product', array('entity.phone' => '_NOT_NULL')
        );

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertContains(
            '1 - 15 of 90',
            $crawler->filter('#main .list-pagination')->text()
        );
    }

    public function testListSortIsUsedForEmbedddLists()
    {
        $crawler = $this->requestEditView('Category', 1);

        $forAttrValue = md5('/admin/?entity=Product&action=embeddedList&filters%5Bentity.category%5D=1');
        $createdAtTh = 'th[data-property-name="createdAt"].sorted';

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertSame(1, $crawler->filter('.embedded-list[for="'.$forAttrValue.'"] '.$createdAtTh)->count());
    }

    public function testDefinedSortIsUsedForEmbedddLists()
    {
        $crawler = $this->getBackendPage(array('entity' => 'Purchase', 'action' => 'embeddedList'));

        $forAttrValue = md5('/admin/?entity=Purchase&action=embeddedList');
        $createdAtTh = 'th[data-property-name="createdAt"].sorted';

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertSame(1, $crawler->filter('.embedded-list[for="'.$forAttrValue.'"] '.$createdAtTh)->count());
    }

    public function testDefaultOpenNewTabConfigForEmbedddLists()
    {
        $crawler = $this->getBackendPage(array('entity' => 'Product', 'action' => 'embeddedList'));

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertSame(0, $crawler->filter('.embedded-list .open-new-tab')->count());
    }

    public function testSetOpenNewTabConfigForEmbedddLists()
    {
        $crawler = $this->getBackendPage(array('entity' => 'Purchase', 'action' => 'embeddedList'));

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertSame(1, $crawler->filter('.embedded-list .open-new-tab')->count());
    }
}
