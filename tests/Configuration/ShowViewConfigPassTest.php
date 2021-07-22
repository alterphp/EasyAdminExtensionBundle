<?php

namespace AlterPHP\EasyAdminExtensionBundle\Tests\Configuration;

use AlterPHP\EasyAdminExtensionBundle\Configuration\ShowViewConfigPass;
use AlterPHP\EasyAdminExtensionBundle\Helper\EmbeddedListHelper;
use PHPUnit\Framework\TestCase;

class ShowViewConfigPassTest extends TestCase
{
    public function testDefaultEmbeddedListShow()
    {
        $embeddedListHelper = $this->createMock(EmbeddedListHelper::class);
        $embeddedListHelper
            ->method('getEntityFqcnFromParent')
            ->with('App\Entity\MyEntity', 'relations')
            ->will($this->returnValue('App\Entity\MyRelation'))
        ;
        $embeddedListHelper
            ->method('guessEntityEntry')
            ->with('App\Entity\MyRelation')
            ->will($this->returnValue('MyRelation'))
        ;

        $showViewConfigPass = new ShowViewConfigPass($embeddedListHelper);

        $backendConfig = [
            'entities' => [
                'MyEntity' => [
                    'show' => [
                        'fields' => [
                            'foo' => ['type' => 'string'],
                            'bar' => ['type' => 'boolean'],
                            'relations' => [
                                'property' => 'relations',
                                'type' => 'embedded_list',
                                'sourceEntity' => 'App\Entity\MyEntity',
                            ],
                            'qux' => ['type' => 'integer'],
                        ],
                    ],
                ],
            ],
        ];

        $backendConfig = $showViewConfigPass->process($backendConfig);

        $expectedBackendConfig = [
            'entities' => [
                'MyEntity' => [
                    'show' => [
                        'fields' => [
                            'foo' => ['type' => 'string'],
                            'bar' => ['type' => 'boolean'],
                            'relations' => [
                                'property' => 'relations',
                                'type' => 'embedded_list',
                                'sourceEntity' => 'App\Entity\MyEntity',
                                'template' => '@EasyAdminExtension/default/field_embedded_list.html.twig',
                                'template_options' => [
                                    'object_type' => 'entity',
                                    'entity' => 'MyRelation',
                                    'object_fqcn' => 'App\Entity\MyRelation',
                                    'parent_object_property' => 'relations',
                                    'ext_filters' => [],
                                    'hidden_fields' => [],
                                    'max_results' => null,
                                    'sort' => null,
                                ],
                            ],
                            'qux' => ['type' => 'integer'],
                        ],
                    ],
                ],
            ],
        ];

        $this->assertSame($backendConfig, $expectedBackendConfig);
    }

    public function testDefinedEmbeddedListShow()
    {
        $embeddedListHelper = $this->createMock(EmbeddedListHelper::class);
        $embeddedListHelper
            ->method('getEntityFqcnFromParent')
            ->with('Foo\Entity\MyEntity', 'children')
            ->will($this->returnValue('App\Entity\MyRelation'))
        ;
        $embeddedListHelper
            ->method('guessEntityEntry')
            ->with('App\Entity\MyRelation')
            ->will($this->returnValue('MyRelation'))
        ;

        $showViewConfigPass = new ShowViewConfigPass($embeddedListHelper);

        $backendConfig = [
            'entities' => [
                'MyEntity' => [
                    'show' => [
                        'fields' => [
                            'relations' => [
                                'property' => 'relations',
                                'type' => 'embedded_list',
                                'sourceEntity' => 'App\Entity\MyEntity',
                                'template' => 'path/to/template.html.twig',
                                'template_options' => [
                                    'entity' => 'Child',
                                    'object_fqcn' => 'Foo\Entity\Child',
                                    'parent_object_fqcn' => 'Foo\Entity\MyEntity',
                                    'parent_object_property' => 'children',
                                    'ext_filters' => ['bar' => 'baz'],
                                    'sort' => ['qux', 'ASC'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $backendConfig = $showViewConfigPass->process($backendConfig);

        $expectedBackendConfig = [
            'entities' => [
                'MyEntity' => [
                    'show' => [
                        'fields' => [
                            'relations' => [
                                'property' => 'relations',
                                'type' => 'embedded_list',
                                'sourceEntity' => 'App\Entity\MyEntity',
                                'template' => 'path/to/template.html.twig',
                                'template_options' => [
                                    'entity' => 'Child',
                                    'object_fqcn' => 'Foo\Entity\Child',
                                    'parent_object_fqcn' => 'Foo\Entity\MyEntity',
                                    'parent_object_property' => 'children',
                                    'ext_filters' => ['bar' => 'baz'],
                                    'sort' => ['field' => 'qux', 'direction' => 'ASC'],
                                    'hidden_fields' => [],
                                    'max_results' => null,
                                    'object_type' => 'entity',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->assertSame($backendConfig, $expectedBackendConfig);
    }
}
