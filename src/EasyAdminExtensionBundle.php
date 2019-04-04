<?php

namespace AlterPHP\EasyAdminExtensionBundle;

use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class EasyAdminExtensionBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $this->addRegisterMappingsPass($container);
    }

    /**
     * Register storage mapping for model-based persisted objects from EasyAdminExtension.
     * Much inspired from FOSUserBundle implementation.
     *
     * @see  https://github.com/FriendsOfSymfony/FOSUserBundle/blob/master/FOSUserBundle.php
     *
     * @param ContainerBuilder $container
     *
     * @throws \ReflectionException
     */
    private function addRegisterMappingsPass(ContainerBuilder $container)
    {
        $easyAdminExtensionBundleRefl = new \ReflectionClass($this);

        if ($easyAdminExtensionBundleRefl->isUserDefined()) {
            $easyAdminExtensionBundlePath = \dirname((string) $easyAdminExtensionBundleRefl->getFileName());
            $easyAdminExtensionDoctrineMapping = $easyAdminExtensionBundlePath.'/Resources/config/doctrine-mapping';

            $mappings = [
                \realpath($easyAdminExtensionDoctrineMapping) => 'AlterPHP\EasyAdminExtensionBundle\Model',
            ];
            if (\class_exists(DoctrineOrmMappingsPass::class)) {
                $container->addCompilerPass(DoctrineOrmMappingsPass::createXmlMappingDriver($mappings));
            }
        }
    }
}
