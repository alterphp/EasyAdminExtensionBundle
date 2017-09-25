<?php

namespace AlterPHP\EasyAdminExtensionBundle\Tests\Configuration;

use AlterPHP\EasyAdminExtensionBundle\Configuration\CustomFormTypeConfigPass;

class CustomFormTypeConfigPassTest extends \PHPUnit_Framework_TestCase
{
    public function testCustomFormTypesAreReplaced()
    {
        $customFormTypesMap = array(
            'foo' => 'AppBundle\Form\type\FooType',
            'bar' => 'AppBundle\Form\type\BarType',
        );

        $customFormTypeConfigPass = new CustomFormTypeConfigPass($customFormTypesMap);

        $backendConfig = array(
            'entites' => array(
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

        $backendConfig = $customFormTypeConfigPass->process($backendConfig);

        $expectedBackendConfig = $backendConfig = array(
            'entites' => array(
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
