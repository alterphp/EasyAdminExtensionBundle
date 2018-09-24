<?php

namespace AlterPHP\EasyAdminExtensionBundle\Configuration;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use EasyCorp\Bundle\EasyAdminBundle\Configuration\ConfigPassInterface;
use EasyCorp\Bundle\EasyAdminBundle\Form\Type\EasyAdminAutocompleteType;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormInterface;

/**
 * Guess form types for list filters.
 */
class ListFiltersConfigPass implements ConfigPassInterface
{
    /** @var ManagerRegistry */
    private $doctrine;

    /** @var FormFactory */
    private $formFactory;

    public function __construct(ManagerRegistry $doctrine, FormFactory $formFactory)
    {
        $this->doctrine = $doctrine;
        $this->formFactory = $formFactory;
    }

    /**
     * @param array $backendConfig
     *
     * @return array
     */
    public function process(array $backendConfig): array
    {
        if (!isset($backendConfig['entities'])) {
            return $backendConfig;
        }

        foreach ($backendConfig['entities'] as $entityName => $entityConfig) {
            if (!isset($entityConfig['list']['filters'])) {
                continue;
            }

            $filters = array();

            foreach ($entityConfig['list']['filters'] as $i => $filter) {
                // Detects invalid config node
                if (!is_string($filter) && !is_array($filter)) {
                    throw new \RuntimeException(
                        sprintf(
                            'The values of the "filters" option for the list view of the "%s" entity can only be strings or arrays.',
                            $entityConfig['class']
                        )
                    );
                }

                // Key mapping
                if (is_string($filter)) {
                    $filterConfig = array('property' => $filter);
                } else {
                    if (!array_key_exists('property', $filter)) {
                        throw new \RuntimeException(
                            sprintf(
                                'One of the values of the "filters" option for the "list" view of the "%s" entity does not define the mandatory option "property".',
                                $entityConfig['class']
                            )
                        );
                    }

                    $filterConfig = $filter;
                }

                $this->configureFilter($entityConfig['class'], $filterConfig);

                // If type is not configured at this steps => not guessable
                if (!isset($filterConfig['type'])) {
                    continue;
                }

                $filters[$filterConfig['property']] = $filterConfig;
            }

            // set filters config and form !
            $backendConfig['entities'][$entityName]['list']['filters'] = $filters;
            $backendConfig['entities'][$entityName]['list']['filtersForm'] = $this->createFiltersForm($filters);
        }

        return $backendConfig;
    }

    private function configureFilter(string $entityClass, array &$filterConfig)
    {
        // No need to guess type
        if (isset($filterConfig['type'])) {
            return;
        }

        $em = $this->doctrine->getManagerForClass($entityClass);
        $entityMetadata = $em->getMetadataFactory()->getMetadataFor($entityClass);

        // Not able to guess type
        if (
            !$entityMetadata->hasField($filterConfig['property'])
            && !$entityMetadata->hasAssociation($filterConfig['property'])
        ) {
            return;
        }

        if ($entityMetadata->hasField($filterConfig['property'])) {
            $this->configureFieldFilter(
                $entityClass, $entityMetadata->getFieldMapping($filterConfig['property']), $filterConfig
            );
        } elseif ($entityMetadata->hasAssociation($filterConfig['property'])) {
            $this->configureAssociationFilter(
                $entityClass, $entityMetadata->getAssociationMapping($filterConfig['property']), $filterConfig
            );
        }
    }

    private function configureFieldFilter(string $entityClass, array $fieldMapping, array &$filterConfig)
    {
        // string => choice (multiple)
        // text => text LIKE %%
        // date/datetime => date range
    }

    private function configureAssociationFilter(string $entityClass, array $associationMapping, array &$filterConfig)
    {
        // To-One
        if ($associationMapping['type'] & ClassMetadataInfo::TO_ONE) {
            $filterConfig['type'] = EasyAdminAutocompleteType::class;
            $filterConfig['type_options']['class'] = $associationMapping['targetEntity'];
            $filterConfig['type_options']['multiple'] = true;
        }
    }

    private function createFiltersForm(array $filters): FormInterface
    {
        $formBuilder = $this->formFactory->createNamedBuilder('list_filters');

        foreach ($filters as $name => $config) {
            $formBuilder->add(
                $name,
                isset($config['type']) ? $config['type'] : null,
                array_merge(
                    array('required' => false),
                    $config['type_options']
                )
            );
        }

        return $formBuilder->getForm();
    }
}
