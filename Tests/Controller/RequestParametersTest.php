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
        $crawler = $this->requestListView('Product', ['entity.enabled' => false]);

        $this->assertEquals(10, $crawler->filter('#main tr[data-id]')->count());
    }

    public function testRequestManySimpleFiltersAreApplied()
    {
        $crawler = $this->requestListView(
            'Product', ['entity.enabled' => false, 'entity.oddEven' => 'even']
        );

        $this->assertEquals(5, $crawler->filter('#main tr[data-id]')->count());
    }
}
