<?php

namespace AlterPHP\EasyAdminExtensionBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @see http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class EasyAdminExtensionExtension extends Extension
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
}
