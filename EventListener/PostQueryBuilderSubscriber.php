<?php

namespace AlterPHP\EasyAdminExtensionBundle\EventListener;

use Doctrine\ORM\QueryBuilder;
use JavierEguiluz\Bundle\EasyAdminBundle\Event\EasyAdminEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class PostQueryBuilderSubscriber implements EventSubscriberInterface 
{
    public static function getSubscribedEvents()
    {
        return array(
            EasyAdminEvents::POST_LIST_QUERY_BUILDER => array('onPostListQueryBuilder'),
            EasyAdminEvents::POST_SEARCH_QUERY_BUILDER => array('onPostSearchQueryBuilder'),
        );
    }

    public function onPostListQueryBuilder(GenericEvent $event)
    {    	
    	$queryBuilder = $event->getArgument('query_builder');

    	if ($event->hasArgument('request')) {
    		$this->applyRequestFilters($queryBuilder, $event->getArgument('request')->get('filters', array()));
    	}
    }

    public function onPostSearchQueryBuilder(GenericEvent $event)
    {	
    	$queryBuilder = $event->getArgument('query_builder');

    	if ($event->hasArgument('request')) {
    		$this->applyRequestFilters($queryBuilder, $event->getArgument('request')->get('filters', array()));
    	}
    }

    protected function applyRequestFilters(QueryBuilder $queryBuilder, array $filters = array())
    {
        foreach ($filters as $field => $value) {
            // Sanitize parameter name
            $parameterName = 'filter_'.str_replace('.', '_', $field);
            $filterDqlPart = $field.' = :'.$parameterName;
            // For multiple value, use an IN clause
            if (is_array($value)) {
                $filterDqlPart = $field.' IN (:'.$parameterName.')';
            }
            $queryBuilder
                ->andWhere($filterDqlPart)
                ->setParameter($parameterName, $value)
            ;
        }
    }
}