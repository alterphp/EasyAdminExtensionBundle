<?php

namespace AlterPHP\EasyAdminExtensionBundle\EventListener;

use AlterPHP\EasyAdminExtensionBundle\Form\Transformer\Operator\GreaterThanModelTransformer;
use AlterPHP\EasyAdminExtensionBundle\Form\Transformer\Operator\GreaterThanOrEqualModelTransformer;
use AlterPHP\EasyAdminExtensionBundle\Form\Transformer\Operator\LowerThanModelTransformer;
use AlterPHP\EasyAdminExtensionBundle\Form\Transformer\Operator\LowerThanOrEqualModelTransformer;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Query\QueryException;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Event\EasyAdminEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Apply filters on list/search queryBuilder.
 */
class PostQueryBuilderSubscriber implements EventSubscriberInterface
{
    protected static $operators = [
        GreaterThanModelTransformer::OPERATOR_PREFIX => '>',
        GreaterThanOrEqualModelTransformer::OPERATOR_PREFIX => '>=',
        LowerThanModelTransformer::OPERATOR_PREFIX => '<',
        LowerThanOrEqualModelTransformer::OPERATOR_PREFIX => '<=',
    ];

    /**
     * @var \AlterPHP\EasyAdminExtensionBundle\Helper\ListFormFiltersHelper
     */
    protected $listFormFiltersHelper;

    /**
     * ListFormFiltersExtension constructor.
     *
     * @param \AlterPHP\EasyAdminExtensionBundle\Helper\ListFormFiltersHelper $listFormFiltersHelper
     */
    public function __construct($listFormFiltersHelper)
    {
        $this->listFormFiltersHelper = $listFormFiltersHelper;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            EasyAdminEvents::POST_LIST_QUERY_BUILDER => ['onPostListQueryBuilder'],
            EasyAdminEvents::POST_SEARCH_QUERY_BUILDER => ['onPostSearchQueryBuilder'],
        ];
    }

    /**
     * Called on POST_LIST_QUERY_BUILDER event.
     *
     * @param GenericEvent $event
     */
    public function onPostListQueryBuilder(GenericEvent $event)
    {
        $queryBuilder = $event->getArgument('query_builder');

        // Request filters
        if ($event->hasArgument('request')) {
            $this->applyRequestFilters($queryBuilder, $event->getArgument('request')->get('filters', []));
        }

        // List form filters
        if ($event->hasArgument('entity')) {
            $entityConfig = $event->getArgument('entity');
            if (isset($entityConfig['list']['form_filters'])) {
                $listFormFiltersForm = $this->listFormFiltersHelper->getListFormFilters($entityConfig['list']['form_filters']);
                if ($listFormFiltersForm->isSubmitted() && $listFormFiltersForm->isValid()) {
                    $this->applyFormFilters($queryBuilder, $listFormFiltersForm->getData());
                }
            }
        }
    }

    /**
     * Called on POST_SEARCH_QUERY_BUILDER event.
     *
     * @param GenericEvent $event
     */
    public function onPostSearchQueryBuilder(GenericEvent $event)
    {
        $queryBuilder = $event->getArgument('query_builder');

        if ($event->hasArgument('request')) {
            $this->applyRequestFilters($queryBuilder, $event->getArgument('request')->get('filters', []));
        }
    }

    /**
     * Applies request filters on queryBuilder.
     *
     * @param QueryBuilder $queryBuilder
     * @param array        $filters
     */
    protected function applyRequestFilters(QueryBuilder $queryBuilder, array $filters = [])
    {
        foreach ($filters as $field => $value) {
            // Empty string and numeric keys is considered as "not applied filter"
            if ('' === $value || \is_int($field)) {
                continue;
            }
            // Add root entity alias if none provided
            $field = false === \strpos($field, '.') ? $queryBuilder->getRootAlias().'.'.$field : $field;
            // Checks if filter is directly appliable on queryBuilder
            if (!$this->isFilterAppliable($queryBuilder, $field)) {
                continue;
            }
            // Sanitize parameter name
            $parameter = 'request_filter_'.\str_replace('.', '_', $field);

            $this->filterQueryBuilder($queryBuilder, $field, $parameter, $value);
        }
    }

    /**
     * Applies form filters on queryBuilder.
     *
     * @param QueryBuilder $queryBuilder
     * @param array        $filters
     */
    protected function applyFormFilters(QueryBuilder $queryBuilder, array $filters = [])
    {
        foreach ($filters as $field => $value) {
            $value = $this->filterEasyadminAutocompleteValue($value);
            // Empty string and numeric keys is considered as "not applied filter"
            if (null === $value || '' === $value || \is_int($field)) {
                continue;
            }
            // Add root entity alias if none provided
            $field = false === \strpos($field, '.') ? $queryBuilder->getRootAlias().'.'.$field : $field;
            // Checks if filter is directly appliable on queryBuilder
            if (!$this->isFilterAppliable($queryBuilder, $field)) {
                continue;
            }
            // Sanitize parameter name
            $parameter = 'form_filter_'.\str_replace('.', '_', $field);

            $this->filterQueryBuilder($queryBuilder, $field, $parameter, $value);
        }
    }

    private function filterEasyadminAutocompleteValue($value)
    {
        if (!\is_array($value) || !isset($value['autocomplete']) || 1 !== \count($value)) {
            return $value;
        }

        return $value['autocomplete'];
    }

    /**
     * Filters queryBuilder.
     *
     * @param QueryBuilder $queryBuilder
     * @param string       $field
     * @param string       $parameter
     * @param mixed        $value
     */
    protected function filterQueryBuilder(QueryBuilder $queryBuilder, string $field, string $parameter, $value)
    {
        switch (true) {
            // Multiple values leads to IN statement
            case $value instanceof Collection:
            case \is_array($value):
                if (0 < \count($value)) {
                    $filterDqlPart = $field.' IN (:'.$parameter.')';
                }
                break;
            // Special value for NULL evaluation
            case '_NULL' === $value:
                $parameter = null;
                $filterDqlPart = $field.' IS NULL';
                break;
            // Special value for NOT NULL evaluation
            case '_NOT_NULL' === $value:
                $parameter = null;
                $filterDqlPart = $field.' IS NOT NULL';
                break;
            // Special case if value has an operatorPrefix
            case $operatorPrefix = $this->getOperatorPrefix($value):
                // get value without prefix
                $value = \substr($value, \strlen($operatorPrefix));

                $operator = static::$operators[$operatorPrefix];

                $filterDqlPart = $field.' '.$operator.' :'.$parameter;
                break;
            // Default is equality
            default:
                $filterDqlPart = $field.' = :'.$parameter;
                break;
        }

        if (isset($filterDqlPart)) {
            $queryBuilder->andWhere($filterDqlPart);
            if (null !== $parameter) {
                $queryBuilder->setParameter($parameter, $value);
            }
        }
    }

    /**
     * Checks if filter is directly appliable on queryBuilder.
     *
     * @param QueryBuilder $queryBuilder
     * @param string       $field
     *
     * @return bool
     */
    protected function isFilterAppliable(QueryBuilder $queryBuilder, string $field): bool
    {
        $qbClone = clone $queryBuilder;

        try {
            $qbClone->andWhere($field.' IS NULL');

            // Generating SQL throws a QueryException if using wrong field/association
            $qbClone->getQuery()->getSQL();
        } catch (QueryException $e) {
            return false;
        }

        return true;
    }

    protected function getOperatorPrefix($value): string
    {
        $operatorPrefixes = \array_keys(static::$operators);

        foreach ($operatorPrefixes as $operatorPrefix) {
            if (0 === \strpos($value, $operatorPrefix)) {
                return $operatorPrefix;
            }
        }

        return '';
    }
}
