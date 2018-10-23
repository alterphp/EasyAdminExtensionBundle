<?php

namespace AlterPHP\EasyAdminExtensionBundle\Tests\Configuration;

use AlterPHP\EasyAdminExtensionBundle\Configuration\ShortFormTypeConfigPass;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ShortFormTypeConfigPassTest extends \PHPUnit_Framework_TestCase
{
    public function testCustomFormTypesAreReplaced()
    {
        $customFormTypesMap = array(
            'foo' => 'AppBundle\Form\Type\FooType',
            'bar' => 'AppBundle\Form\Type\BarType',
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
                            'testField1' => array('type' => 'AppBundle\Form\Type\FooType'),
                        ),
                    ),
                    'edit' => array(
                        'fields' => array(
                            'testField2' => array('type' => 'AppBundle\Form\Type\BarType'),
                        ),
                    ),
                    'new' => array(
                        'fields' => array(
                            'testField1' => array('type' => 'AppBundle\Form\Type\FooType'),
                            'testField2' => array('type' => 'AppBundle\Form\Type\BarType'),
                        ),
                    ),
                ),
            ),
        );

        $this->assertSame($backendConfig, $expectedBackendConfig);
    }

    public function testLegacyShortFormTypesAreReplaced()
    {
        // Legacy short form types may be overriden by configuration
        $customFormTypesMap = array(
            'text' => 'AppBundle\Form\Type\TextType',
        );

        $shortFormTypeConfigPass = new ShortFormTypeConfigPass($customFormTypesMap);

        $backendConfig = array(
            'entities' => array(
                'TestEntity' => array(
                    'new' => array('fields' => array(
                        'testField1' => array('type' => 'text'),
                        'testField2' => array('type' => 'choice'),
                    )),
                ),
            ),
        );

        $backendConfig = $shortFormTypeConfigPass->process($backendConfig);

        $expectedBackendConfig = array(
            'entities' => array(
                'TestEntity' => array(
                    'new' => array(
                        'fields' => array(
                            'testField1' => array('type' => 'AppBundle\Form\Type\TextType'),
                            'testField2' => array('type' => ChoiceType::class),
                        ),
                    ),
                ),
            ),
        );

        $this->assertSame($backendConfig, $expectedBackendConfig);
    }
}
