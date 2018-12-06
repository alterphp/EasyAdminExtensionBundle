<?php

namespace AlterPHP\EasyAdminExtensionBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ListFormFiltersExtension extends AbstractExtension
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

    public function getFunctions()
    {
        return array(
            new TwigFunction('list_form_filters', array($this, 'getListFormFilters')),
        );
    }

    public function getListFormFilters(array $filters)
    {
        return $this->listFormFiltersHelper->getListFormFilters($filters)->createView();
    }
}
