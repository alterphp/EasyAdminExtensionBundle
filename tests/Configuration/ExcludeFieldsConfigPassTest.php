<?php

namespace AlterPHP\EasyAdminExtensionBundle\Tests\Configuration;

use AlterPHP\EasyAdminExtensionBundle\Configuration\ExcludeFieldsConfigPass;
use AlterPHP\EasyAdminExtensionBundle\Exception\ConflictingConfigurationException;
use AppTestBundle\Entity\FunctionalTests\Dummy;
use PHPUnit\Framework\TestCase;

class ExcludeFieldsConfigPassTest extends TestCase
{
    /**
     * @var ExcludeFieldsConfigPass
     */
    private $excludeFieldsConfigPass;

    protected function setUp(): void
    {
        $this->excludeFieldsConfigPass = new ExcludeFieldsConfigPass();
    }

    public function testExcludeFields()
    {
        $backendConfig = [
            'entities' => [
                'TestEntity' => [
                    'class' => Dummy::class,
                    'form' => [
                        'exclude_fields' => ['exclude'],
                    ],
                ],
            ],
        ];

        $processedBackendConfig = $this->excludeFieldsConfigPass->process($backendConfig);

        $expectedBackendConfig = [
            'entities' => [
                'TestEntity' => [
                    'class' => Dummy::class,
                    'form' => [
                        'exclude_fields' => ['exclude'],
                        'fields' => ['name', 'title'],
                    ],
                ],
            ],
        ];

        $this->assertSame($processedBackendConfig, $expectedBackendConfig);
    }

    public function testExcludeFieldThrowsConflictingConfigurationException()
    {
        $backendConfig = [
            'entities' => [
                'TestEntity' => [
                    'class' => Dummy::class,
                    'form' => [
                        'fields' => ['name', 'title'],
                        'exclude_fields' => ['exclude'],
                    ],
                ],
            ],
        ];

        $this->expectException(ConflictingConfigurationException::class);

        $processedBackendConfig = $this->excludeFieldsConfigPass->process($backendConfig);
    }
}
