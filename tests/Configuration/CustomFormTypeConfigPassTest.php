<?php

namespace AlterPHP\EasyAdminExtensionBundle\Tests\Configuration;

use AlterPHP\EasyAdminExtensionBundle\Configuration\ShortFormTypeConfigPass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ShortFormTypeConfigPassTest extends TestCase
{
    public function testCustomFormTypesAreReplaced()
    {
        $customFormTypesMap = [
            'foo' => 'AppBundle\Form\Type\FooType',
            'bar' => 'AppBundle\Form\Type\BarType',
        ];

        $shortFormTypeConfigPass = new ShortFormTypeConfigPass($customFormTypesMap);

        $backendConfig = [
            'entities' => [
                'TestEntity' => [
                    'form' => ['fields' => ['testField1' => ['type' => 'foo']]],
                    'edit' => ['fields' => ['testField2' => ['type' => 'bar']]],
                    'new' => ['fields' => [
                        'testField1' => ['type' => 'foo'],
                        'testField2' => ['type' => 'bar'],
                    ]],
                ],
            ],
            'documents' => [
                'TestDocument' => [
                    'form' => ['fields' => ['testField1' => ['type' => 'foo']]],
                    'edit' => ['fields' => ['testField2' => ['type' => 'bar']]],
                    'new' => ['fields' => [
                        'testField1' => ['type' => 'foo'],
                        'testField2' => ['type' => 'bar'],
                    ]],
                ],
            ],
        ];

        $backendConfig = $shortFormTypeConfigPass->process($backendConfig);

        $expectedBackendConfig = [
            'entities' => [
                'TestEntity' => [
                    'form' => [
                        'fields' => [
                            'testField1' => ['type' => 'AppBundle\Form\Type\FooType'],
                        ],
                    ],
                    'edit' => [
                        'fields' => [
                            'testField2' => ['type' => 'AppBundle\Form\Type\BarType'],
                        ],
                    ],
                    'new' => [
                        'fields' => [
                            'testField1' => ['type' => 'AppBundle\Form\Type\FooType'],
                            'testField2' => ['type' => 'AppBundle\Form\Type\BarType'],
                        ],
                    ],
                ],
            ],
            'documents' => [
                'TestDocument' => [
                    'form' => [
                        'fields' => [
                            'testField1' => ['type' => 'AppBundle\Form\Type\FooType'],
                        ],
                    ],
                    'edit' => [
                        'fields' => [
                            'testField2' => ['type' => 'AppBundle\Form\Type\BarType'],
                        ],
                    ],
                    'new' => [
                        'fields' => [
                            'testField1' => ['type' => 'AppBundle\Form\Type\FooType'],
                            'testField2' => ['type' => 'AppBundle\Form\Type\BarType'],
                        ],
                    ],
                ],
            ],
        ];

        $this->assertSame($backendConfig, $expectedBackendConfig);
    }

    public function testLegacyShortFormTypesAreReplaced()
    {
        // Legacy short form types may be overriden by configuration
        $customFormTypesMap = [
            'text' => 'AppBundle\Form\Type\TextType',
        ];

        $shortFormTypeConfigPass = new ShortFormTypeConfigPass($customFormTypesMap);

        $backendConfig = [
            'entities' => [
                'TestEntity' => [
                    'new' => ['fields' => [
                        'testField1' => ['type' => 'text'],
                        'testField2' => ['type' => 'choice'],
                    ]],
                ],
            ],
            'documents' => [
                'TestDocument' => [
                    'new' => ['fields' => [
                        'testField1' => ['type' => 'text'],
                        'testField2' => ['type' => 'choice'],
                    ]],
                ],
            ],
        ];

        $backendConfig = $shortFormTypeConfigPass->process($backendConfig);

        $expectedBackendConfig = [
            'entities' => [
                'TestEntity' => [
                    'new' => [
                        'fields' => [
                            'testField1' => ['type' => 'AppBundle\Form\Type\TextType'],
                            'testField2' => ['type' => ChoiceType::class],
                        ],
                    ],
                ],
            ],
            'documents' => [
                'TestDocument' => [
                    'new' => [
                        'fields' => [
                            'testField1' => ['type' => 'AppBundle\Form\Type\TextType'],
                            'testField2' => ['type' => ChoiceType::class],
                        ],
                    ],
                ],
            ],
        ];

        $this->assertSame($backendConfig, $expectedBackendConfig);
    }
}
