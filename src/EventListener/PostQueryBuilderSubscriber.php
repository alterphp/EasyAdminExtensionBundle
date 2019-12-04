<?php

namespace AlterPHP\EasyAdminExtensionBundle\EventListener;

use AlterPHP\EasyAdminExtensionBundle\Model\ListFilter;
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
     */
    public function onPostListQueryBuilder(GenericEvent $event)
    {
        $queryBuilder = $event->getArgument('query_builder');

        // Request filters
        if ($event->hasArgument('request')) {
            $this->applyRequestFilters($queryBuilder, $event->getArgument('request')->get('ext_filters', []));
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
     */
    public function onPostSearchQueryBuilder(GenericEvent $event)
    {
        $queryBuilder = $event->getArgument('query_builder');

        if ($event->hasArgument('request')) {
            $this->applyRequestFilters($queryBuilder, $event->getArgument('request')->get('ext_filters', []));
        }
    }

    /**
     * Applies request filters on queryBuilder.
     */
    protected function applyRequestFilters(QueryBuilder $queryBuilder, array $filters = [])
    {
        foreach ($filters as $field => $value) {
            // Empty string and numeric keys is considered as "not applied filter"
            if ('' === $value || \is_int($field)) {
                continue;
            }

            $operator = \is_array($value) ? ListFilter::OPERATOR_IN : ListFilter::OPERATOR_EQUALS;
            $listFilter = ListFilter::createFromRequest($field, $operator, $value);

            $this->filterQueryBuilder($queryBuilder, $field, $listFilter);
        }
    }

    /**
     * Applies form filters on queryBuilder.
     */
    protected function applyFormFilters(QueryBuilder $queryBuilder, array $filters = [])
    {
        foreach ($filters as $field => $listFilter) {
            if (null === $listFilter) {
                continue;
            }

            $this->filterQueryBuilder($queryBuilder, $field, $listFilter);
        }
    }

    /**
     * Filters queryBuilder.
     */
    protected function filterQueryBuilder(QueryBuilder $queryBuilder, string $field, ListFilter $listFilter)
    {
        $value = $this->filterEasyadminAutocompleteValue($listFilter->getValue());
        // Empty string and numeric keys is considered as "not applied filter"
        if (null === $value || '' === $value || \is_numeric($field)) {
            return;
        }

        // Add root entity alias if none provided
        $queryField = $listFilter->getProperty();
        if (false === \strpos($queryField, '.')) {
            $queryField = $queryBuilder->getRootAlias().'.'.$queryField;
        }

        // Checks if filter is directly appliable on queryBuilder
        if (!$this->isFilterAppliable($queryBuilder, $queryField)) {
            return;
        }

        $operator = $listFilter->getOperator();
        // Sanitize parameter name
        $parameter = 'form_filter_'.\str_replace('.', '_', $field);

        switch ($operator) {
            case ListFilter::OPERATOR_EQUALS:
                if ('_NULL' === $value) {
                    $queryBuilder->andWhere(\sprintf('%s IS NULL', $queryField));
                } elseif ('_NOT_NULL' === $value) {
                    $queryBuilder->andWhere(\sprintf('%s IS NOT NULL', $queryField));
                } else {
                    $queryBuilder
                        ->andWhere(\sprintf('%s %s :%s', $queryField, '=', $parameter))
                        ->setParameter($parameter, $value)
                    ;
                }
                break;
            case ListFilter::OPERATOR_NOT:
                $queryBuilder
                    ->andWhere(\sprintf('%s %s :%s', $queryField, '!=', $parameter))
                    ->setParameter($parameter, $value)
                ;
                break;
            case ListFilter::OPERATOR_IN:
                // Checks that $value is not an empty Traversable
                if (0 < \count($value)) {
                    $queryBuilder
                        ->andWhere(\sprintf('%s %s (:%s)', $queryField, 'IN', $parameter))
                        ->setParameter($parameter, $value)
                    ;
                }
                break;
            case ListFilter::OPERATOR_NOTIN:
                // Checks that $value is not an empty Traversable
                if (0 < \count($value)) {
                    $queryBuilder
                        ->andWhere(\sprintf('%s %s (:%s)', $queryField, 'NOT IN', $parameter))
                        ->setParameter($parameter, $value)
                    ;
                }
                break;
            case ListFilter::OPERATOR_GT:
                $queryBuilder
                    ->andWhere(\sprintf('%s %s :%s', $queryField, '>', $parameter))
                    ->setParameter($parameter, $value)
                ;
                break;
            case ListFilter::OPERATOR_GTE:
                $queryBuilder
                    ->andWhere(\sprintf('%s %s :%s', $queryField, '>=', $parameter))
                    ->setParameter($parameter, $value)
                ;
                break;
            case ListFilter::OPERATOR_LT:
                $queryBuilder
                    ->andWhere(\sprintf('%s %s :%s', $queryField, '<', $parameter))
                    ->setParameter($parameter, $value)
                ;
                break;
            case ListFilter::OPERATOR_LTE:
                $queryBuilder
                    ->andWhere(\sprintf('%s %s :%s', $queryField, '<=', $parameter))
                    ->setParameter($parameter, $value)
                ;
                break;
            case ListFilter::OPERATOR_LIKE:
                $queryBuilder
                    ->andWhere(\sprintf('%s %s :%s', $queryField, 'LIKE', $parameter))
                    ->setParameter($parameter, '%'.$value.'%')
                ;
                break;
            default:
                throw new \RuntimeException(\sprintf('Operator "%s" is not supported !', $operator));
        }
    }

    protected function filterEasyadminAutocompleteValue($value)
    {
        if (!\is_array($value) || !isset($value['autocomplete']) || 1 !== \count($value)) {
            return $value;
        }

        return $value['autocomplete'];
    }

    /**
     * Checks if filter is directly appliable on queryBuilder.
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
}
