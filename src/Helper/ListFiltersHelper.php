<?php

declare(strict_types=1);

namespace AlterPHP\EasyAdminExtensionBundle\Helper;

use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * This file is part of the EasyAdmin Extension package.
 */
class ListFiltersHelper
{
    /**
     * @var FormFactory
     */
    private $formFactory;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var FormInterface
     */
    private $listFiltersForm;

    /**
     * @param FormFactory $formFactory
     */
    public function __construct(FormFactory $formFactory, RequestStack $requestStack)
    {
        $this->formFactory = $formFactory;
        $this->requestStack = $requestStack;
    }

    public function getListFiltersForm(array $filters): FormInterface
    {
        if (!isset($this->listFiltersForm)) {
            $formBuilder = $this->formFactory->createNamedBuilder('list_filters');

            foreach ($filters as $name => $config) {
                $formBuilder->add(
                    $name,
                    isset($config['type']) ? $config['type'] : null,
                    array_merge(
                        array('required' => false),
                        $config['type_options']
                    )
                );
            }

            $this->listFiltersForm = $formBuilder->setMethod('GET')->getForm();
            $this->listFiltersForm->handleRequest($this->requestStack->getCurrentRequest());
        }

        return $this->listFiltersForm;
    }
}
