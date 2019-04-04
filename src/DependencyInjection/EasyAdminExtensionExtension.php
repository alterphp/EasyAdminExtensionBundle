<?php

namespace AlterPHP\EasyAdminExtensionBundle\DependencyInjection;

use EasyCorp\Bundle\EasyAdminBundle\EasyAdminBundle;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @see http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class EasyAdminExtensionExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('easy_admin_extension.custom_form_types', $config['custom_form_types']);
        $container->setParameter('easy_admin_extension.minimum_role', $config['minimum_role']);
        $container->setParameter(
            'easy_admin_extension.embedded_list.open_new_tab',
            $config['embedded_list']['open_new_tab']
        );

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');
    }

    public function prepend(ContainerBuilder $container)
    {
        $twigConfigs = $container->getExtensionConfig('twig');

        $paths = [];
        // keeping user-configured paths
        foreach ($twigConfigs as $twigConfig) {
            if (isset($twigConfig['default_path'])) {
                $userDefinedTwigDefaultPath = $twigConfig['default_path'];
            }
            if (isset($twigConfig['paths'])) {
                $paths += $twigConfig['paths'];
            }
        }

        $twigDefaultPathDefaultValue = $container->getParameterBag()->resolveValue('%kernel.project_dir%/templates');
        $twigDefaultPath = $userDefinedTwigDefaultPath ?? $twigDefaultPathDefaultValue;

        // Waiting this PR or any alternative is implemented by Symfony itself
        // @see https://github.com/symfony/symfony/pull/30527
        // Put back user default path
        $userDefaultPath = $twigDefaultPath.'/bundles/EasyAdminBundle/';
        if (file_exists($userDefaultPath)) {
            $paths[$userDefaultPath] = 'EasyAdmin';
        }

        // EasyAdminExtension overrides EasyAdmin templates
        $paths[\dirname(__DIR__).'/Resources/views/'] = 'EasyAdmin';

        $container->prependExtensionConfig('twig', ['paths' => $paths]);
    }
}
