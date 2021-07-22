<?php

namespace AlterPHP\EasyAdminExtensionBundle\EventListener;

use AlterPHP\EasyAdminExtensionBundle\Model\ListFilter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

abstract class AbstractPostQueryBuilderSubscriber implements EventSubscriberInterface
{
    /**
     * @var \AlterPHP\EasyAdminExtensionBundle\Helper\ListFormFiltersHelper
     */
    protected $listFormFiltersHelper;

    /**
     * Tests if $queryBuilder is supported.
     *
     * @param object $queryBuilder
     *
     * @return bool
     */
    abstract protected function supportsQueryBuilder($queryBuilder): bool;

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
     * Called on POST_LIST_QUERY_BUILDER event.
     *
     * @param GenericEvent $event
     */
    public function onPostListQueryBuilder(GenericEvent $event)
    {
        $queryBuilder = $event->getArgument('query_builder');

        if (!$this->supportsQueryBuilder($queryBuilder)) {
            throw new \RuntimeException('Passed queryBuilder is not supported !');
        }

        // Request filters
        if ($event->hasArgument('request')) {
            $this->applyRequestFilters($queryBuilder, $event->getArgument('request')->get('ext_filters', []));
        }

        // List form filters
        if ($event->hasArgument(static::APPLIABLE_OBJECT_TYPE)) {
            $objectConfig = $event->getArgument(static::APPLIABLE_OBJECT_TYPE);
            if (isset($objectConfig['list']['form_filters'])) {
                $listFormFiltersForm = $this->listFormFiltersHelper->getListFormFilters($objectConfig['list']['form_filters']);
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

        if (!$this->supportsQueryBuilder($queryBuilder)) {
            throw new \RuntimeException('Passed queryBuilder is not supported !');
        }

        if ($event->hasArgument('request')) {
            $this->applyRequestFilters($queryBuilder, $event->getArgument('request')->get('ext_filters', []));
        }
    }

    /**
     * Applies request filters on queryBuilder.
     *
     * @param object $queryBuilder
     * @param array  $filters
     */
    protected function applyRequestFilters($queryBuilder, array $filters = [])
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
     *
     * @param object $queryBuilder
     * @param array  $filters
     */
    protected function applyFormFilters($queryBuilder, array $filters = [])
    {
        foreach ($filters as $field => $listFilter) {
            if (null === $listFilter) {
                continue;
            }

            $this->filterQueryBuilder($queryBuilder, $field, $listFilter);
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
     *
     * @param object $queryBuilder
     * @param string $field
     *
     * @return bool
     */
    protected function isFilterAppliable($queryBuilder, string $field): bool
    {
        return true;
    }
}
