<?php

namespace AlterPHP\EasyAdminExtensionBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ListFiltersExtension extends AbstractExtension
{
    protected $listFiltersHelper;

    public function __construct($listFiltersHelper)
    {
        $this->listFiltersHelper = $listFiltersHelper;
    }

    public function getFunctions()
    {
        return array(
            new TwigFunction('list_filters_form', array($this, 'getListFiltersForm')),
        );
    }

    public function getListFiltersForm(array $filters)
    {
        return $this->listFiltersHelper->getListFiltersForm($filters)->createView();
    }
}
