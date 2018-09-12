<?php

namespace AlterPHP\EasyAdminExtensionBundle\Tests\Configuration;

use AlterPHP\EasyAdminExtensionBundle\Configuration\ExcludeFieldsConfigPass;
use AlterPHP\EasyAdminExtensionBundle\Exception\ConflictingConfigurationException;
use AppTestBundle\Entity\FunctionalTests\Dummy;

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

    public function testExcludeFields()
    {
        $backendConfig = array(
            'entities' => array(
                'TestEntity' => array(
                    'class' => Dummy::class,
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
                    'class' => Dummy::class,
                    'form' => array(
                        'exclude_fields' => array('exclude'),
                        'fields' => array('name', 'title')
                    ),
                ),
            ),
        );

        $this->assertSame($processedBackendConfig, $expectedBackendConfig);
    }

    public function testExcludeFieldThrowsConflictingConfigurationException()
    {
        $backendConfig = array(
            'entities' => array(
                'TestEntity' => array(
                    'class' => Dummy::class,
                    'form' => array(
                        'fields' => array('name', 'title'),
                        'exclude_fields' => array('exclude'),
                    ),
                ),
            ),
        );

        $this->expectException(ConflictingConfigurationException::class);

        $processedBackendConfig = $this->excludeFieldsConfigPass->process($backendConfig);
    }
}
