<?php

namespace AlterPHP\EasyAdminExtensionBundle\Twig;

use AlterPHP\EasyAdminExtensionBundle\Helper\ListFormFiltersHelper;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ListFormFiltersExtension extends AbstractExtension
{
    /**
     * @var ListFormFiltersHelper
     */
    protected $listFiltersHelper;

    /**
     * ListFormFiltersExtension constructor.
     *
     * @param ListFormFiltersHelper $listFiltersHelper
     */
    public function __construct(ListFormFiltersHelper $listFiltersHelper)
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
