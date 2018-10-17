<?php

namespace AlterPHP\EasyAdminExtensionBundle\Twig;

use AlterPHP\EasyAdminExtensionBundle\Helper\EmbeddedListHelper;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class EmbeddedListExtension extends AbstractExtension
{
    /**
     * @var EmbeddedListHelper
     */
    protected $embeddedListHelper;

    /**
     * EmbeddedListExtension constructor.
     *
     * @param EmbeddedListHelper $embeddedListHelper
     */
    public function __construct(EmbeddedListHelper $embeddedListHelper)
    {
        $this->embeddedListHelper = $embeddedListHelper;
    }

    public function getFunctions()
    {
        return array(
            new TwigFunction('guess_default_filters', array($this, 'guessDefaultFilters')),
        );
    }

    public function guessDefaultFilters(string $entityFqcn, string $parentEntityProperty, $parentEntity)
    {
        return $this->embeddedListHelper->guessDefaultFilter($entityFqcn, $parentEntityProperty, $parentEntity);
    }
}
