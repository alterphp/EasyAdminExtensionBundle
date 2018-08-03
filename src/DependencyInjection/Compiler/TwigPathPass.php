<?php

namespace AlterPHP\EasyAdminExtensionBundle\DependencyInjection\Compiler;

use AlterPHP\EasyAdminExtensionBundle\EasyAdminExtensionBundle;
use EasyCorp\Bundle\EasyAdminBundle\EasyAdminBundle;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class TwigPathPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $twigLoaderFilesystemId = $container->getAlias('twig.loader')->__toString();
        $twigLoaderFilesystemDefinition = $container->getDefinition($twigLoaderFilesystemId);

        // Replaces native EasyAdmin templates
        $easyAdminExtensionBundleRefl = new \ReflectionClass(EasyAdminExtensionBundle::class);
        if ($easyAdminExtensionBundleRefl->isUserDefined()) {
            $easyAdminExtensionBundlePath = dirname((string) $easyAdminExtensionBundleRefl->getFileName());
            $easyAdminExtensionTwigPath = $easyAdminExtensionBundlePath.'/Resources/views';
            $twigLoaderFilesystemDefinition->addMethodCall(
                'prependPath',
                array($easyAdminExtensionTwigPath, 'EasyAdmin')
            );
        }

        $nativeEasyAdminBundleRefl = new \ReflectionClass(EasyAdminBundle::class);
        if ($nativeEasyAdminBundleRefl->isUserDefined()) {
            $nativeEasyAdminBundlePath = dirname((string) $nativeEasyAdminBundleRefl->getFileName());
            $nativeEasyAdminTwigPath = $nativeEasyAdminBundlePath.'/Resources/views';
            // Defines a namespace from native EasyAdmin templates
            $twigLoaderFilesystemDefinition->addMethodCall(
                'addPath',
                array($nativeEasyAdminTwigPath, 'BaseEasyAdmin')
            );
        }
    }
}
