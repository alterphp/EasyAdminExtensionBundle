<?php

namespace AlterPHP\EasyAdminExtensionBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('easy_admin_extension');
        if (method_exists($treeBuilder, 'getRootNode')) {
            $rootNode = $treeBuilder->getRootNode();
        } else {
            // BC layer for symfony/config 4.1 and older
            $rootNode = $treeBuilder->root('easy_admin_extension');
        }

        $rootNode
            ->children()
                ->arrayNode('custom_form_types')
                    ->useAttributeAsKey('short_name')
                    ->prototype('scalar')
                        ->validate()
                            ->ifTrue(function ($v) {
                                return !\class_exists($v);
                            })
                                ->thenInvalid('Class %s for custom type does not exist !')
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('minimum_role')
                    ->defaultNull()
                ->end()
                ->arrayNode('embedded_list')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('open_new_tab')
                            ->defaultTrue()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
