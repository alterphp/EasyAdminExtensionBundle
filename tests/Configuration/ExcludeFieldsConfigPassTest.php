<?php

namespace AlterPHP\EasyAdminExtensionBundle\Tests\Configuration;

use AlterPHP\EasyAdminExtensionBundle\Configuration\ExcludeFieldsConfigPass;
use AlterPHP\EasyAdminExtensionBundle\Tests\Configuration\ExcludeFieldsConfigPassSource\DummyEntity;

class ExcludeFieldsConfigPassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ExcludeFieldsConfigPass
     */
    private $excludeFieldsConfigPass;

    protected function setUp()
    {
        $this->excludeFieldsConfigPass = new ExcludeFieldsConfigPass();
    }

    public function test()
    {
        $backendConfig = array(
            'entities' => array(
                'TestEntity' => array(
                    'class' => DummyEntity::class,
                    'form' => array(
                        'exclude_fields' => array('exclude')
                    ),
                ),
            ),
        );

        $processedBackendConfig = $this->excludeFieldsConfigPass->process($backendConfig);

        $expectedBackendConfig = array(
            'entities' => array(
                'TestEntity' => array(
                    'class' => DummyEntity::class,
                    'form' => array(
                        'exclude_fields' => array('exclude'),
                        'fields' => array('name')
                    ),
                ),
            ),
        );

        $this->assertSame($processedBackendConfig, $expectedBackendConfig);
    }
}
