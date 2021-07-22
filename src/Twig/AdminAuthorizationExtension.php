<?php

namespace AlterPHP\EasyAdminExtensionBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AdminAuthorizationExtension extends AbstractExtension
{
    /**
     * @var \AlterPHP\EasyAdminExtensionBundle\Security\AdminAuthorizationChecker
     */
    protected $adminAuthorizationChecker;

    public function __construct($adminAuthorizationChecker)
    {
        $this->adminAuthorizationChecker = $adminAuthorizationChecker;
    }

    public function getFilters()
    {
        return [
            new TwigFilter('prune_item_actions', [$this, 'pruneItemsActions']),
        ];
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('is_easyadmin_granted', [$this, 'isEasyAdminGranted']),
        ];
    }

    public function isEasyAdminGranted(array $objectConfig, string $actionName = 'list', $subject = null)
    {
        return $this->adminAuthorizationChecker->isEasyAdminGranted($objectConfig, $actionName, $subject);
    }

    public function pruneItemsActions(
        array $itemActions, array $objectConfig, array $forbiddenActions = [], $subject = null
    ) {
        return \array_filter($itemActions, function ($action) use ($objectConfig, $forbiddenActions, $subject) {
            return !\in_array($action, $forbiddenActions)
                    && $this->isEasyAdminGranted($objectConfig, $action, $subject)
            ;
        }, ARRAY_FILTER_USE_KEY);
    }
}
