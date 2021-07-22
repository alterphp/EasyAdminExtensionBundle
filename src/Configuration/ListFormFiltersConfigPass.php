<?php

namespace AlterPHP\EasyAdminExtensionBundle\Configuration;

use AlterPHP\EasyAdminExtensionBundle\Model\ListFilter;
use Doctrine\DBAL\Types\Type as DBALType;
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
        if (isset($backendConfig['entities']) && \is_array($backendConfig['entities'])) {
            $this->processObjectListFormFilters('entity', $backendConfig['entities']);
        }

        if (isset($backendConfig['documents']) && \is_array($backendConfig['documents'])) {
            $this->processObjectListFormFilters('document', $backendConfig['documents']);
        }

        return $backendConfig;
    }

    private function processObjectListFormFilters(string $objectType, array &$objectConfigs)
    {
        foreach ($objectConfigs as $objectName => $objectConfig) {
            if (!isset($objectConfig['list']['form_filters'])) {
                continue;
            }

            $formFilters = [];

            foreach ($objectConfig['list']['form_filters'] as $key => $formFilter) {
                // Detects invalid config node
                if (!\is_string($formFilter) && !\is_array($formFilter)) {
                    throw new \RuntimeException(\sprintf('The values of the "form_filters" option for the list view of the "%s" object of type "%s" can only be strings or arrays.', $objectConfig['class'], $objectType));
                }

                // Key mapping
                if (\is_string($formFilter)) {
                    $filterConfig = ['property' => $formFilter];
                } else {
                    if (!\array_key_exists('property', $formFilter)) {
                        if (\is_string($key)) {
                            $formFilter['property'] = $key;
                        } else {
                            throw new \RuntimeException(\sprintf('One of the values of the "form_filters" option for the "list" view of the "%s" object of type "%s" does not define the mandatory option "property".', $objectConfig['class'], $objectType));
                        }
                    }

                    $filterConfig = $formFilter;
                }

                // Auto set name with property value
                $filterConfig['name'] = $filterConfig['name'] ?? $filterConfig['property'];
                // Auto set label with name value
                $filterConfig['label'] = $filterConfig['label'] ?? $filterConfig['name'];
                // Auto-set translation_domain
                $filterConfig['translation_domain'] = $filterConfig['translation_domain'] ?? $objectConfig['translation_domain'];

                if ('entity' === $objectType) {
                    $this->configureEntityFormFilter($objectConfig['class'], $filterConfig);
                }

                // If type is not configured at this steps => not guessable
                if (!isset($filterConfig['type'])) {
                    continue;
                }

                $formFilters[$filterConfig['name']] = $filterConfig;
            }

            // set form filters config and form !
            $objectConfigs[$objectName]['list']['form_filters'] = $formFilters;
        }
    }

    private function configureEntityFormFilter(string $entityClass, array &$filterConfig)
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

        if ($entityMetadata->hasField($filterConfig['property'])) {
            $this->configureEntityPropertyFilter($entityClass, $entityMetadata->getFieldMapping($filterConfig['property']), $filterConfig);
        } elseif ($entityMetadata->hasAssociation($filterConfig['property'])) {
            $this->configureEntityAssociationFilter(
                $entityClass, $entityMetadata->getAssociationMapping($filterConfig['property']), $filterConfig
            );
        }
    }

    private function configureEntityPropertyFilter(string $entityClass, array $fieldMapping, array &$filterConfig)
    {
        $defaultFilterConfigTypeOptions = [];

        switch ($fieldMapping['type']) {
            case DBALType::BOOLEAN:
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
            case DBALType::STRING:
                $filterConfig['operator'] = $filterConfig['operator'] ?? ListFilter::OPERATOR_IN;
                $filterConfig['type'] = $filterConfig['type'] ?? ChoiceType::class;
                if (ChoiceType::class === $filterConfig['type']) {
                    $defaultFilterConfigTypeOptions['placeholder'] = '-';
                    $defaultFilterConfigTypeOptions['choices'] = $this->getChoiceList($entityClass, $filterConfig['property'], $filterConfig);
                    $defaultFilterConfigTypeOptions['attr'] = ['data-widget' => 'select2'];
                    $defaultFilterConfigTypeOptions['choice_translation_domain'] = $filterConfig['translation_domain'];
                }
                break;
            case DBALType::SMALLINT:
            case DBALType::INTEGER:
            case DBALType::BIGINT:
                $filterConfig['operator'] = $filterConfig['operator'] ?? ListFilter::OPERATOR_EQUALS;
                $filterConfig['type'] = $filterConfig['type'] ?? IntegerType::class;
                break;
            case DBALType::DECIMAL:
            case DBALType::FLOAT:
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

    private function configureEntityAssociationFilter(string $entityClass, array $associationMapping, array &$filterConfig)
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
