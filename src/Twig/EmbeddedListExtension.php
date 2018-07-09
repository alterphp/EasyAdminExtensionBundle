<?php

namespace AlterPHP\EasyAdminExtensionBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class EmbeddedListExtension extends AbstractExtension
{
    protected $embeddedListHelper;

    public function __construct($embeddedListHelper)
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
