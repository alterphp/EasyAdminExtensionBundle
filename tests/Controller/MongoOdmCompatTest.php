<?php

namespace AlterPHP\EasyAdminExtensionBundle\Tests\Controller;

use AlterPHP\EasyAdminExtensionBundle\Tests\Fixtures\AbstractTestCase;

class MongoOdmCompatTest extends AbstractTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->initClient(['environment' => 'mongo_odm_compat', 'withMongoOdm' => true]);
    }

    public function testEasyAdminWorks()
    {
        $crawler = $this->requestListView('Product');

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
    }

    public function testEasyAdminMongoOdmWorks()
    {
        $this->markTestSkipped('The MongoDB test database is not yet available.');

        $crawler = $this->requestMongoOdmListView('RequestLog');

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @return Crawler
     */
    protected function requestMongoOdmListView($documentName = 'RequestLog', array $requestFilters = [], array $formFilters = [])
    {
        return $this->getMongoOdmBackendPage([
            'action' => 'list',
            'document' => $documentName,
            'view' => 'list',
            'ext_filters' => $requestFilters,
            'form_filters' => $formFilters,
        ]);
    }
}
