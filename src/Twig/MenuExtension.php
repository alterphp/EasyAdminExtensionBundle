<?php

namespace AlterPHP\EasyAdminExtensionBundle\Twig;

use Symfony\Component\Form\FormView;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class MenuExtension extends AbstractExtension
{
    protected $menuHelper;

    public function __construct($menuHelper)
    {
        $this->menuHelper = $menuHelper;
    }

    public function getFilters()
    {
        return array(
            new TwigFilter('prune_menu_items', array($this, 'pruneMenuItems')),
        );
    }

    public function pruneMenuItems(array $menuConfig, array $entitiesConfig)
    {
        return $this->menuHelper->pruneMenuItems($menuConfig, $entitiesConfig);
    }
}
