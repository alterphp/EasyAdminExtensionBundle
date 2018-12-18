<?php

namespace AlterPHP\EasyAdminExtensionBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class EmbeddedListExtension extends AbstractExtension
{
    /**
     * @var \AlterPHP\EasyAdminExtensionBundle\Helper\EmbeddedListHelper
     */
    protected $embeddedListHelper;

    /**
     * EmbeddedListExtension constructor.
     *
     * @param \AlterPHP\EasyAdminExtensionBundle\Helper\EmbeddedListHelper $embeddedListHelper
     */
    public function __construct($embeddedListHelper)
    {
        $this->embeddedListHelper = $embeddedListHelper;
    }

    public function getFilters()
    {
        return [
            new TwigFilter('embedded_list_identifier', [$this, 'getEmbeddedListIdentifier']),
        ];
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('guess_default_filters', [$this, 'guessDefaultFilters']),
        ];
    }

    public function getEmbeddedListIdentifier(string $requestUri)
    {
        return \md5($requestUri);
    }

    public function guessDefaultFilters(string $entityFqcn, string $parentEntityProperty, $parentEntity)
    {
        return $this->embeddedListHelper->guessDefaultFilter($entityFqcn, $parentEntityProperty, $parentEntity);
    }
}
