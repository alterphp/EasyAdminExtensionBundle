<?php

namespace AlterPHP\EasyAdminExtensionBundle\Twig;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
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

    public function isEasyAdminGranted(array $entity, string $actionName)
    {
        try {
            $this->adminAuthorizationChecker->checksUserAccess($entity, $actionName);
        } catch (AccessDeniedException $e) {
            return false;
        }

        return true;
    }

    public function pruneItemsActions(array $itemActions, array $entity)
    {
        return array_filter($itemActions, function ($action) use ($entity) {
            return $this->isEasyAdminGranted($entity, $action);
        }, ARRAY_FILTER_USE_KEY);
    }
}
