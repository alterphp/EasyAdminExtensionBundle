<?php

namespace AlterPHP\EasyAdminExtensionBundle\Configuration;

use AlterPHP\EasyAdminExtensionBundle\Model\ListFilter;
use Doctrine\DBAL\Types\Type as DBALType;
use Doctrine\DBAL\Types\Types as DBALTypes;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\Persistence\ManagerRegistry;
use EasyCorp\Bundle\EasyAdminBundle\Configuration\ConfigPassInterface;
use EasyCorp\Bundle\EasyAdminBundle\Form\Type\EasyAdminAutocompleteType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

/**
 * Guess form types for list form filters.
 */
class ListFormFiltersConfigPass implements ConfigPassInterface
{
    /** @var ManagerRegistry */
    private $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function process(array $backendConfig): array
    {
        if (!isset($backendConfig['entities'])) {
            return $backendConfig;
        }

        foreach ($backendConfig['entities'] as $entityName => $entityConfig) {
            if (!isset($entityConfig['list']['form_filters'])) {
                continue;
            }

            $formFilters = [];

            foreach ($entityConfig['list']['form_filters'] as $i => $formFilter) {
                // Detects invalid config node
                if (!\is_string($formFilter) && !\is_array($formFilter)) {
                    throw new \RuntimeException(\sprintf('The values of the "form_filters" option for the list view of the "%s" entity can only be strings or arrays.', $entityConfig['class']));
                }

                // Key mapping
                if (\is_string($formFilter)) {
                    $filterConfig = ['property' => $formFilter];
                } else {
                    if (!\array_key_exists('property', $formFilter)) {
                        throw new \RuntimeException(\sprintf('One of the values of the "form_filters" option for the "list" view of the "%s" entity does not define the mandatory option "property".', $entityConfig['class']));
                    }

                    $filterConfig = $formFilter;
                }

                // Auto set name with property value
                $filterConfig['name'] = $filterConfig['name'] ?? $filterConfig['property'];
                // Auto set label with name value
                $filterConfig['label'] = $filterConfig['label'] ?? $filterConfig['name'];
                // Auto-set translation_domain
                $filterConfig['translation_domain'] = $filterConfig['translation_domain'] ?? $entityConfig['translation_domain'];

                $this->configureFilter($entityConfig['class'], $filterConfig);

                // If type is not configured at this steps => not guessable
                if (!isset($filterConfig['type'])) {
                    continue;
                }

                $formFilters[$filterConfig['name']] = $filterConfig;
            }

            // set form filters config and form !
            $backendConfig['entities'][$entityName]['list']['form_filters'] = $formFilters;
        }

        return $backendConfig;
    }

    private function configureFilter(string $entityClass, array &$filterConfig)
    {
        $em = $this->doctrine->getManagerForClass($entityClass);
        $entityMetadata = $em->getMetadataFactory()->getMetadataFor($entityClass);

        // Not able to guess type
        if (
            !$entityMetadata->hasField($filterConfig['property'])
            && !$entityMetadata->hasAssociation($filterConfig['property'])
        ) {
            return;
        }

        // Only applicable to ORM ClassMetadataInfo instances
        if ($entityMetadata instanceof ClassMetadataInfo) {
            if ($entityMetadata->hasField($filterConfig['property'])) {
                $this->configureFieldFilter(
                    $entityClass, $entityMetadata->getFieldMapping($filterConfig['property']), $filterConfig);
            } elseif ($entityMetadata->hasAssociation($filterConfig['property'])) {
                $this->configureAssociationFilter(
                    $entityClass, $entityMetadata->getAssociationMapping($filterConfig['property']), $filterConfig
                );
            }
        }
    }

    private function configureFieldFilter(string $entityClass, array $fieldMapping, array &$filterConfig)
    {
        $defaultFilterConfigTypeOptions = [];

        switch ($fieldMapping['type']) {
            case (DBALTypes::BOOLEAN ?? DBALType::BOOLEAN):
                $filterConfig['operator'] = $filterConfig['operator'] ?? ListFilter::OPERATOR_EQUALS;
                $filterConfig['type'] = $filterConfig['type'] ?? ChoiceType::class;
                if (ChoiceType::class === $filterConfig['type']) {
                    $defaultFilterConfigTypeOptions['placeholder'] = '-';
                    $defaultFilterConfigTypeOptions['choices'] = [
                            'list_form_filters.default.boolean.true' => true,
                            'list_form_filters.default.boolean.false' => false,
                        ];
                    $defaultFilterConfigTypeOptions['attr'] = ['data-widget' => 'select2'];
                    $defaultFilterConfigTypeOptions['choice_translation_domain'] = 'EasyAdminBundle';
                }
                break;
            case (DBALTypes::STRING ?? DBALType::STRING):
                $filterConfig['operator'] = $filterConfig['operator'] ?? ListFilter::OPERATOR_IN;
                $filterConfig['type'] = $filterConfig['type'] ?? ChoiceType::class;
                if (ChoiceType::class === $filterConfig['type']) {
                    $defaultFilterConfigTypeOptions['placeholder'] = '-';
                    $defaultFilterConfigTypeOptions['choices'] = $this->getChoiceList($entityClass, $filterConfig['property'], $filterConfig);
                    $defaultFilterConfigTypeOptions['attr'] = ['data-widget' => 'select2'];
                    $defaultFilterConfigTypeOptions['choice_translation_domain'] = $filterConfig['translation_domain'];
                }
                break;
            case (DBALTypes::SMALLINT ?? DBALType::SMALLINT):
            case (DBALTypes::INTEGER ?? DBALType::INTEGER):
            case (DBALTypes::BIGINT ?? DBALType::BIGINT):
                $filterConfig['operator'] = $filterConfig['operator'] ?? ListFilter::OPERATOR_EQUALS;
                $filterConfig['type'] = $filterConfig['type'] ?? IntegerType::class;
                break;
            case (DBALTypes::DECIMAL ?? DBALType::DECIMAL):
            case (DBALTypes::FLOAT ?? DBALType::FLOAT):
                $filterConfig['operator'] = $filterConfig['operator'] ?? ListFilter::OPERATOR_EQUALS;
                $filterConfig['type'] = $filterConfig['type'] ?? NumberType::class;
                break;
            default:
                return;
        }

        // Auto-set ChoiceType options
        if (ChoiceType::class === $filterConfig['type']) {
            $this->autosetChoiceTypeOptions($entityClass, $defaultFilterConfigTypeOptions, $filterConfig);
        }

        // Merge default type options
        $filterConfig['type_options'] = \array_merge(
            $defaultFilterConfigTypeOptions,
            $filterConfig['type_options'] ?? []
        );
    }

    private function configureAssociationFilter(string $entityClass, array $associationMapping, array &$filterConfig)
    {
        $defaultFilterConfigTypeOptions = [];

        // To-One (EasyAdminAutocompleteType)
        if ($associationMapping['type'] & ClassMetadataInfo::TO_ONE) {
            $filterConfig['operator'] = $filterConfig['operator'] ?? ListFilter::OPERATOR_IN;
            $filterConfig['type'] = $filterConfig['type'] ?? EasyAdminAutocompleteType::class;
        }

        // Auto-set EasyAdminAutocompleteType options
        if (EasyAdminAutocompleteType::class === $filterConfig['type']) {
            $defaultFilterConfigTypeOptions['class'] = $associationMapping['targetEntity'];

            if (\in_array($filterConfig['operator'], [ListFilter::OPERATOR_IN, ListFilter::OPERATOR_NOTIN])) {
                $defaultFilterConfigTypeOptions['multiple'] = $defaultFilterConfigTypeOptions['multiple'] ?? true;
            }
        }

        // Auto-set ChoiceType options
        if (ChoiceType::class === $filterConfig['type']) {
            $this->autosetChoiceTypeOptions($entityClass, $defaultFilterConfigTypeOptions, $filterConfig);
        }

        // Merge default type options
        $filterConfig['type_options'] = \array_merge(
            $defaultFilterConfigTypeOptions,
            $filterConfig['type_options'] ?? []
        );
    }

    private function autosetChoiceTypeOptions(string $entityClass, array &$defaultFilterConfigTypeOptions, array &$filterConfig)
    {
        $defaultFilterConfigTypeOptions['choices'] = $defaultFilterConfigTypeOptions['choices'] ?? $this->getChoiceList($entityClass, $filterConfig['property'], $filterConfig);

        if (\in_array($filterConfig['operator'], [ListFilter::OPERATOR_IN, ListFilter::OPERATOR_NOTIN])) {
            $defaultFilterConfigTypeOptions['multiple'] = $defaultFilterConfigTypeOptions['multiple'] ?? true;
        }
    }

    private function getChoiceList(string $entityClass, string $property, array &$filterConfig)
    {
        if (isset($filterConfig['type_options']['choices'])) {
            $choices = $filterConfig['type_options']['choices'];
            unset($filterConfig['type_options']['choices']);

            return $choices;
        }

        if (!isset($filterConfig['type_options']['choices_static_callback'])) {
            throw new \RuntimeException(\sprintf('Choice filter field "%s" for entity "%s" must provide either a static callback method returning choice list or choices option.', $property, $entityClass));
        }

        $callableParams = [];
        if (\is_string($filterConfig['type_options']['choices_static_callback'])) {
            $callable = [$entityClass, $filterConfig['type_options']['choices_static_callback']];
        } else {
            $callable = [$entityClass, $filterConfig['type_options']['choices_static_callback'][0]];
            $callableParams = $filterConfig['type_options']['choices_static_callback'][1];
        }
        unset($filterConfig['type_options']['choices_static_callback']);

        return \forward_static_call_array($callable, $callableParams);
    }
}
