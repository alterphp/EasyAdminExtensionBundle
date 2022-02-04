<?php

namespace AlterPHP\EasyAdminExtensionBundle\EventListener;

use AlterPHP\EasyAdminExtensionBundle\Model\CustomListFilter;
use AlterPHP\EasyAdminExtensionBundle\Model\ListFilter;
use Doctrine\ORM\Query\QueryException;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Event\EasyAdminEvents;

/**
 * Apply filters on list/search queryBuilder.
 */
class PostQueryBuilderSubscriber extends AbstractPostQueryBuilderSubscriber
{
    protected const APPLIABLE_OBJECT_TYPE = 'entity';

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
     * {@inheritdoc}
     */
    protected function supportsQueryBuilder($queryBuilder): bool
    {
        return $queryBuilder instanceof QueryBuilder;
    }

    /**
     * Filters queryBuilder.
     */
    protected function filterQueryBuilder(QueryBuilder $queryBuilder, string $field, ListFilter $listFilter)
    {
        if ($listFilter instanceof CustomListFilter) {
            $listFilter->filter($queryBuilder);

            return;
        }

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

    /**
     * {@inheritdoc}
     */
    protected function isFilterAppliable($queryBuilder, string $field): bool
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
