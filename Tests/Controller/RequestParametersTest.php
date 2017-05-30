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

    public function testRequestSingleSimpleFilterIsApplied()
    {
        $crawler = $this->requestListView('Product', array('entity.enabled' => false));

        $this->assertSame(10, $crawler->filter('#main tr[data-id]')->count());
    }

    public function testRequestManySimpleFiltersAreApplied()
    {
        $crawler = $this->requestListView(
            'Product', array('entity.enabled' => false, 'entity.oddEven' => 'even')
        );

        $this->assertSame(5, $crawler->filter('#main tr[data-id]')->count());
    }

    public function testRequestFiltersArePassedToSearchForm()
    {
        $crawler = $this->requestListView(
            'Product', array('entity.enabled' => false, 'entity.oddEven' => 'even')
        );

        $formCrawler = $crawler->filter('.action-search form');

        $this->assertSame(
            1,
            $formCrawler->filter('input[name="filters[entity.enabled]"][value="0"]')->count()
        );
        $this->assertSame(
            1,
            $formCrawler->filter('input[name="filters[entity.oddEven]"][value="even"]')->count()
        );
    }
}
