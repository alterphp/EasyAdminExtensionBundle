<?php

namespace AlterPHP\EasyAdminExtensionBundle\DependencyInjection\Compiler;

use AlterPHP\EasyAdminMongoOdmBundle\EasyAdminMongoOdmBundle;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class MongoOdmPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $mongoOdmBundleClassExists = \class_exists(EasyAdminMongoOdmBundle::class);
        $mongoOdmBundleLoaded = \in_array(EasyAdminMongoOdmBundle::class, $container->getParameter('kernel.bundles'));
        $hasEasyAdminMongoOdmBundle = $mongoOdmBundleClassExists && $mongoOdmBundleLoaded;

        // Disable services specific to EasyAdminMongoOdmBundle
        if (!$hasEasyAdminMongoOdmBundle) {
            $container->removeDefinition('alterphp.easyadmin_extension.subscriber.mongo_odm_post_query_builder');
        }
    }
}
