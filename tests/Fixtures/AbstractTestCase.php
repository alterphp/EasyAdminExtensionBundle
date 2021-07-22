<?php

namespace AlterPHP\EasyAdminExtensionBundle\Tests\Fixtures;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

abstract class AbstractTestCase extends WebTestCase
{
    /** @var Client */
    protected static $client;
    protected static $options = [];

    protected function setUp(): void
    {
        $this->initClient();
        $this->initDatabase();
    }

    protected function initClient(array $options = [])
    {
        static::ensureKernelShutdown();

        static::$client = static::createClient($options + static::$options);
    }

    /**
     * {@inheritdoc}
     */
    protected static function createKernel(array $options = [])
    {
        $kernel = parent::createKernel($options);

        $withMongoOdm = isset($options['withMongoOdm']) ? (bool) $options['withMongoOdm'] : false;

        $kernel->withMongoOdm($withMongoOdm);

        return $kernel;
    }

    /**
     * It ensures that the database contains the original fixtures of the
     * application. This way tests can modify its contents safely without
     * interfering with subsequent tests.
     */
    protected function initDatabase()
    {
        $buildDir = __DIR__.'/../../build';
        $originalDbPath = $buildDir.'/original_test.db';
        $targetDbPath = $buildDir.'/test.db';

        if (!\file_exists($originalDbPath)) {
            throw new \RuntimeException(\sprintf("The fixtures file used for the tests (%s) doesn't exist. This means that the execution of the bootstrap.php script that generates that file failed. Open %s/bootstrap.php and replace `NullOutput as ConsoleOutput` by `ConsoleOutput` to see the actual errors in the console.", $originalDbPath, \realpath(__DIR__.'/..')));
        }

        \copy($originalDbPath, $targetDbPath);
    }

    /**
     * @return Crawler
     */
    protected function getBackendPage(array $queryParameters = [], array $serverParameters = [])
    {
        return static::$client->request('GET', '/admin/?'.\http_build_query($queryParameters, '', '&'), [], [], $serverParameters);
    }

    /**
     * @param array $queryParameters
     *
     * @return Crawler
     */
    protected function getMongoOdmBackendPage(array $queryParameters = [])
    {
        return $this->client->request('GET', '/admin-mongo-odm/?'.\http_build_query($queryParameters, '', '&'));
    }

    /**
     * @return Crawler
     */
    protected function getBackendHomepage()
    {
        return $this->getBackendPage(['entity' => 'Category', 'view' => 'list']);
    }

    /**
     * @return Crawler
     */
    protected function requestListView($entityName = 'Category', array $requestFilters = [], array $formFilters = [])
    {
        return $this->getBackendPage([
            'action' => 'list',
            'entity' => $entityName,
            'view' => 'list',
            'ext_filters' => $requestFilters,
            'form_filters' => $formFilters,
        ]);
    }

    /**
     * @return Crawler
     */
    protected function requestShowView($entityName = 'Category', $entityId = 200)
    {
        return $this->getBackendPage([
            'action' => 'show',
            'entity' => $entityName,
            'id' => $entityId,
        ]);
    }

    /**
     * @return Crawler
     */
    protected function requestSearchView($searchQuery = 'cat', $entityName = 'Category', array $requestFilters = [])
    {
        return $this->getBackendPage([
            'action' => 'search',
            'entity' => $entityName,
            'query' => $searchQuery,
            'ext_filters' => $requestFilters,
        ]);
    }

    /**
     * @return Crawler
     */
    protected function requestNewView($entityName = 'Category')
    {
        return $this->getBackendPage([
            'action' => 'new',
            'entity' => $entityName,
        ]);
    }

    /**
     * @return Crawler
     */
    protected function requestNewAjaxView($entityName = 'Category')
    {
        $this->getBackendPage([
            'action' => 'newAjax',
            'entity' => $entityName,
        ]);
        $response = \json_decode(static::$client->getResponse()->getContent(), true);

        return new Crawler($response['html'], static::$client->getRequest()->getUri());
    }

    /**
     * @return Crawler
     */
    protected function requestEditView($entityName = 'Category', $entityId = '200')
    {
        return $this->getBackendPage([
            'action' => 'edit',
            'entity' => $entityName,
            'id' => $entityId,
        ]);
    }
}
