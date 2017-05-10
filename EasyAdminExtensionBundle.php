<?php

namespace AlterPHP\EasyAdminExtensionBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class EasyAdminExtensionBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        // Prepend EasyAdminExtension bundle tmeplates to EasyAdmin namespace
        $container->prependExtensionConfig(
            'twig',
            ['paths' => [__DIR__.'/Resources/views' => 'EasyAdmin']]
        );
    }
}
