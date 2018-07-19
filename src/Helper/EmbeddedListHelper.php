<?php

declare(strict_types=1);

namespace AlterPHP\EasyAdminExtensionBundle\Helper;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * This file is part of the EasyAdmin Extension package.
 */
class EmbeddedListHelper
{
    /**
     * @var ManagerRegistry
     */
    private $doctrine;

    /**
     * @var array
     */
    private $easyAdminConfig;

    /**
     * @param ManagerRegistry $doctrine
     * @param array           $easyAdminConfig
     */
    public function __construct(ManagerRegistry $doctrine, array $easyAdminConfig)
    {
        $this->doctrine = $doctrine;
        $this->easyAdminConfig = $easyAdminConfig;
    }

    /**
     * Returns EasyAdmin entity entry name for a parent FQCN and property for embedded list.
     *
     * @param string $parentFqcn
     * @param string $parentProperty
     *
     * @return mixed
     *
     * @throws \RuntimeException
     */
    public function getEntityFqcnFromParent(string $parentFqcn, string $parentProperty)
    {
        $parentClassMetadata = $this->doctrine->getManagerForClass($parentFqcn)->getClassMetadata($parentFqcn);

        // Required to use getAssociationMappings method
        if (!$parentClassMetadata instanceof ClassMetadataInfo) {
            return;
        }

        try {
            $entityFqcn = $parentClassMetadata->getAssociationTargetClass($parentProperty);
        } catch (\Exception $e) {
            return;
        }

        return $entityFqcn;
    }

    /**
     * Returns EasyAdmin entity entry name for a FQCN.
     *
     * @param string $entityFqcn
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    public function guessEntityEntry(string $entityFqcn)
    {
        $matchingEntityConfigs = array_filter(
            $this->easyAdminConfig['entities'],
            function ($entityConfig) use ($entityFqcn) {
                return $entityFqcn === $entityConfig['class'];
            }
        );

        if (empty($matchingEntityConfigs)) {
            throw new \RuntimeException(
                sprintf('No entity defined in EasyAdmin configuration matches %s FQCN.', $entityFqcn)
            );
        }

        if (1 < count($matchingEntityConfigs)) {
            throw new \RuntimeException(
                sprintf('More than 1 entity defined in EasyAdmin configuration matches %s FQCN.', $entityFqcn)
            );
        }

        return key($matchingEntityConfigs);
    }

    /**
     * Returns default filter for embeddedList.
     *
     * @param string $entityFqcn
     * @param string $parentEntityProperty
     * @param object $parentEntity
     *
     * @return array
     */
    public function guessDefaultFilter(string $entityFqcn, string $parentEntityProperty, $parentEntity)
    {
        $entityClassMetadata = $this->doctrine->getManagerForClass($entityFqcn)->getClassMetadata($entityFqcn);

        // Required to use getAssociationMappings method
        if (!$entityClassMetadata instanceof ClassMetadataInfo) {
            return [];
        }

        $entityAssociations = $entityClassMetadata->getAssociationMappings();
        $parentEntityFqcn = get_class($parentEntity);
        foreach ($entityAssociations as $assoc) {
            if (
                $parentEntityFqcn === $assoc['targetEntity']
                && $parentEntityProperty === $assoc['inversedBy']
                && 1 === count($assoc['joinColumns'])
            ) {
                $assocFieldPart = 'entity.'.$assoc['fieldName'];
                $assocIdentifierValue = PropertyAccess::createPropertyAccessor()->getValue(
                    $parentEntity, $assoc['joinColumns'][0]['referencedColumnName']
                );

                return [$assocFieldPart => $assocIdentifierValue];
            }
        }

        return [];
    }
}
