<?php

namespace AlterPHP\EasyAdminExtensionBundle\Tests\Configuration;

use AlterPHP\EasyAdminExtensionBundle\Configuration\EmbeddedListViewConfigPass;

class EmbeddedListViewConfigPassTest extends \PHPUnit_Framework_TestCase
{
    public function testOpenNewTabOption()
    {
        $embeddedListViewConfigPass = new EmbeddedListViewConfigPass(true);

        $backendConfig = array(
            'entities' => array(
                'NotSetEntity' => array(
                ),
                'SetTrueEntity' => array(
                    'embeddedList' => array('open_new_tab' => true)
                ),
                'SetFalseEntity' => array(
                    'embeddedList' => array('open_new_tab' => false)
                ),
            ),
        );

        $backendConfig = $embeddedListViewConfigPass->process($backendConfig);

        $expectedBackendConfig = array(
            'entities' => array(
                'NotSetEntity' => array(
                    'embeddedList' => array(
                        'open_new_tab' => true,
                    ),
                ),
                'SetTrueEntity' => array(
                    'embeddedList' => array(
                        'open_new_tab' => true,
                    ),
                ),
                'SetFalseEntity' => array(
                    'embeddedList' => array(
                        'open_new_tab' => false,
                    ),
                ),
            ),
        );

        $this->assertSame($backendConfig, $expectedBackendConfig);
    }
}
