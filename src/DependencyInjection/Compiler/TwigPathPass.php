<?php

namespace AlterPHP\EasyAdminExtensionBundle\DependencyInjection\Compiler;

use AlterPHP\EasyAdminExtensionBundle\EasyAdminExtensionBundle;
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
            $easyAdminExtensionBundlePath = \dirname((string) $easyAdminExtensionBundleRefl->getFileName());
            $easyAdminExtensionTwigPath = $easyAdminExtensionBundlePath.'/Resources/views';
            $twigLoaderFilesystemDefinition->addMethodCall(
                'prependPath',
                [$easyAdminExtensionTwigPath, 'EasyAdminMongoOdm']
            );

            // Waiting this PR or any alternative is implemented by Symfony itself
            // @see https://github.com/symfony/symfony/pull/30527
            // Put back user default path
            $twigDefaultPath = $container->getParameterBag()->resolveValue('%twig.default_path%');
            $userDefaultPath = $twigDefaultPath.'/bundles/EasyAdminMongoOdmBundle/';
            if (\file_exists($userDefaultPath)) {
                $twigLoaderFilesystemDefinition->addMethodCall(
                    'prependPath',
                    [$userDefaultPath, 'EasyAdminMongoOdm']
                );
            }
        }
    }
}
