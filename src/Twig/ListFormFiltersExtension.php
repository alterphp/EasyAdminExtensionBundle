<?php

namespace AlterPHP\EasyAdminExtensionBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ListFormFiltersExtension extends AbstractExtension
{
    protected $listFiltersHelper;

    public function __construct($listFiltersHelper)
    {
        $this->listFiltersHelper = $listFiltersHelper;
    }

    public function getFunctions()
    {
        return array(
            new TwigFunction('list_form_filters', array($this, 'getListFormFilters')),
        );
    }

    public function getListFormFilters(array $filters)
    {
        return $this->listFiltersHelper->getListFiltersForm($filters)->createView();
    }
}
