<?php

namespace AlterPHP\EasyAdminExtensionBundle\Tests\Configuration;

use AlterPHP\EasyAdminExtensionBundle\Configuration\ShortFormTypeConfigPass;

class ShortFormTypeConfigPassTest extends \PHPUnit_Framework_TestCase
{
    public function testCustomFormTypesAreReplaced()
    {
        $customFormTypesMap = array(
            'foo' => 'AppBundle\Form\type\FooType',
            'bar' => 'AppBundle\Form\type\BarType',
        );

        $shortFormTypeConfigPass = new ShortFormTypeConfigPass($customFormTypesMap);

        $backendConfig = array(
            'entities' => array(
                'TestEntity' => array(
                    'form' => array('fields' => array('testField1' => array('type' => 'foo'))),
                    'edit' => array('fields' => array('testField2' => array('type' => 'bar'))),
                    'new' => array('fields' => array(
                        'testField1' => array('type' => 'foo'),
                        'testField2' => array('type' => 'bar'),
                    )),
                ),
            ),
        );

        $backendConfig = $shortFormTypeConfigPass->process($backendConfig);

        $expectedBackendConfig = array(
            'entities' => array(
                'TestEntity' => array(
                    'form' => array(
                        'fields' => array(
                            'testField1' => array('type' => 'AppBundle\Form\type\FooType'),
                        ),
                    ),
                    'edit' => array(
                        'fields' => array(
                            'testField2' => array('type' => 'AppBundle\Form\type\BarType'),
                        ),
                    ),
                    'new' => array(
                        'fields' => array(
                            'testField1' => array('type' => 'AppBundle\Form\type\FooType'),
                            'testField2' => array('type' => 'AppBundle\Form\type\BarType'),
                        ),
                    ),
                ),
            ),
        );

        $this->assertSame($backendConfig, $expectedBackendConfig);
    }
}
