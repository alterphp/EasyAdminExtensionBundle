<?php

namespace AlterPHP\EasyAdminExtensionBundle\Tests\Configuration;

use AlterPHP\EasyAdminExtensionBundle\Configuration\ListFormFiltersConfigPass;
use Doctrine\Common\Persistence\ManagerRegistry;

class ListFormFiltersConfigPassTest extends \PHPUnit_Framework_TestCase
{
    public function testDefinedListFormFilters()
    {
        $doctrineOrm = $this->createMock(ManagerRegistry::class);

        $listFormFiltersConfigPass = new ListFormFiltersConfigPass($doctrineOrm);

        $backendConfig = [
            'entities' => [
                'TestEntity' => [
                    'class' => 'App\\Entity\\TestEntity',
                    'list' => ['form_filters' => [
                        'filter1' => ['type' => 'foo'],
                        'filter2' => ['type' => 'bar'],
                    ]],
                ],
            ],
            'documents' => [
                'TestDocument' => [
                    'class' => 'App\\Document\\TestDocument',
                    'list' => ['form_filters' => [
                        'filter1' => ['type' => 'foo'],
                        'filter2' => ['type' => 'bar'],
                    ]],
                ],
            ],
        ];

        $backendConfig = $listFormFiltersConfigPass->process($backendConfig);

        $expectedBackendConfig = [
            'entities' => [
                'TestEntity' => [
                    'class' => 'App\\Entity\\TestEntity',
                    'list' => ['form_filters' => [
                        'filter1' => ['type' => 'foo', 'property' => 'filter1'],
                        'filter2' => ['type' => 'bar', 'property' => 'filter2'],
                    ]],
                ],
            ],
            'documents' => [
                'TestDocument' => [
                    'class' => 'App\\Document\\TestDocument',
                    'list' => ['form_filters' => [
                        'filter1' => ['type' => 'foo', 'property' => 'filter1'],
                        'filter2' => ['type' => 'bar', 'property' => 'filter2'],
                    ]],
                ],
            ],
        ];

        $this->assertSame($backendConfig, $expectedBackendConfig);
    }
}
