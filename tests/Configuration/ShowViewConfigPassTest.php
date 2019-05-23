<?php

namespace AlterPHP\EasyAdminExtensionBundle\Tests\Configuration;

use AlterPHP\EasyAdminExtensionBundle\Configuration\ShowViewConfigPass;
use AlterPHP\EasyAdminExtensionBundle\Helper\EmbeddedListHelper;

class ShowViewConfigPassTest extends \PHPUnit_Framework_TestCase
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
                                    'entity_fqcn' => 'App\Entity\MyRelation',
                                    'parent_entity_property' => 'relations',
                                    'entity' => 'MyRelation',
                                    'filters' => [],
                                    'hidden_fields' => [],
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
                                    'parent_entity_fqcn' => 'Foo\Entity\MyEntity',
                                    'parent_entity_property' => 'children',
                                    'entity_fqcn' => 'Foo\Entity\Child',
                                    'entity' => 'Child',
                                    'filters' => ['bar' => 'baz'],
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
                                    'parent_entity_fqcn' => 'Foo\Entity\MyEntity',
                                    'parent_entity_property' => 'children',
                                    'entity_fqcn' => 'Foo\Entity\Child',
                                    'entity' => 'Child',
                                    'filters' => ['bar' => 'baz'],
                                    'sort' => ['field' => 'qux', 'direction' => 'ASC'],
                                    'hidden_fields' => [],
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
