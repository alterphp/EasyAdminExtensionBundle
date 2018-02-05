<?php

namespace AlterPHP\EasyAdminExtensionBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AdminAuthorizationExtension extends AbstractExtension
{
    protected $adminAuthorizationChecker;

    public function __construct($adminAuthorizationChecker)
    {
        $this->adminAuthorizationChecker = $adminAuthorizationChecker;
    }

    public function getFunctions()
    {
        return array(
            new TwigFunction('is_easyadmin_granted', array($this, 'isEasyAdminGranted')),
        );
    }

    public function getFilters()
    {
        return array(
            new TwigFilter('prune_item_actions', array($this, 'pruneItemsActions')),
        );
    }

    public function isEasyAdminGranted(array $entity, string $actionName = 'list')
    {
        return $this->adminAuthorizationChecker->isEasyAdminGranted($entity, $actionName);
    }

    public function pruneItemsActions(array $itemActions, array $entity, array $forbiddenActions = [])
    {
        return array_filter($itemActions, function ($action) use ($entity, $forbiddenActions) {
            return !in_array($action, $forbiddenActions) && $this->isEasyAdminGranted($entity, $action);
        }, ARRAY_FILTER_USE_KEY);
    }
}
