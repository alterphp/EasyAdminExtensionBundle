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
     * @param string $embeddedListFieldName
     * @param object $targetEntity
     *
     * @return array
     */
    public function guessDefaultFilter(string $entityFqcn, string $embeddedListFieldName, $targetEntity)
    {
        $entityClassMetadata = $this->doctrine->getManagerForClass($entityFqcn)->getClassMetadata($entityFqcn);

        // Required to use getAssociationMappings method
        if (!$entityClassMetadata instanceof ClassMetadataInfo) {
            return [];
        }

        $entityAssociations = $entityClassMetadata->getAssociationMappings();
        $targetEntityFqcn = get_class($targetEntity);
        foreach ($entityAssociations as $assoc) {
            if (
                $targetEntityFqcn === $assoc['targetEntity']
                && $embeddedListFieldName === $assoc['inversedBy']
                && 1 === count($assoc['joinColumns'])
            ) {
                $assocFieldPart = 'entity.'.$assoc['fieldName'];
                $assocIdentifierValue = PropertyAccess::createPropertyAccessor()->getValue(
                    $targetEntity, $assoc['joinColumns'][0]['referencedColumnName']
                );

                return [$assocFieldPart => $assocIdentifierValue];
            }
        }

        return [];
    }
}
