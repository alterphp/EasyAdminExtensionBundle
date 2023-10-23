<?php

namespace AlterPHP\EasyAdminExtensionBundle\Tests\Configuration;

use AlterPHP\EasyAdminExtensionBundle\Configuration\EmbeddedListViewConfigPass;
use PHPUnit\Framework\TestCase;

class EmbeddedListViewConfigPassTest extends TestCase
{
    public function testOpenNewTabOption()
    {
        $embeddedListViewConfigPass = new EmbeddedListViewConfigPass(true);

        $backendConfig = [
            'entities' => [
                'NotSetEntity' => [
                ],
                'SetTrueEntity' => [
                    'embeddedList' => ['open_new_tab' => true],
                ],
                'SetFalseEntity' => [
                    'embeddedList' => ['open_new_tab' => false],
                ],
            ],
            'documents' => [
                'NotSetDocument' => [
                ],
                'SetTrueDocument' => [
                    'embeddedList' => ['open_new_tab' => true],
                ],
                'SetFalseDocument' => [
                    'embeddedList' => ['open_new_tab' => false],
                ],
            ],
        ];

        $backendConfig = $embeddedListViewConfigPass->process($backendConfig);

        $expectedBackendConfig = [
            'entities' => [
                'NotSetEntity' => [
                    'embeddedList' => [
                        'template' => '@EasyAdminExtension/default/embedded_list.html.twig',
                        'open_new_tab' => true,
                    ],
                ],
                'SetTrueEntity' => [
                    'embeddedList' => [
                        'open_new_tab' => true,
                        'template' => '@EasyAdminExtension/default/embedded_list.html.twig',
                    ],
                ],
                'SetFalseEntity' => [
                    'embeddedList' => [
                        'open_new_tab' => false,
                        'template' => '@EasyAdminExtension/default/embedded_list.html.twig',
                    ],
                ],
            ],
            'documents' => [
                'NotSetDocument' => [
                    'embeddedList' => [
                        'open_new_tab' => true,
                    ],
                ],
                'SetTrueDocument' => [
                    'embeddedList' => [
                        'open_new_tab' => true,
                    ],
                ],
                'SetFalseDocument' => [
                    'embeddedList' => [
                        'open_new_tab' => false,
                    ],
                ],
            ],
        ];

        $this->assertSame($backendConfig, $expectedBackendConfig);
    }
}
