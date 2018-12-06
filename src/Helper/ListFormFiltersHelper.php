<?php

declare(strict_types=1);

namespace AlterPHP\EasyAdminExtensionBundle\Helper;

use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * This file is part of the EasyAdmin Extension package.
 */
class ListFormFiltersHelper
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
     * @var bool
     */
    private $formCsrfEnabled;

    /**
     * @param FormFactory  $formFactory
     * @param RequestStack $requestStack
     * @param bool         $formCsrfEnabled
     */
    public function __construct(FormFactory $formFactory, RequestStack $requestStack, $formCsrfEnabled)
    {
        $this->formFactory = $formFactory;
        $this->requestStack = $requestStack;
        $this->formCsrfEnabled = $formCsrfEnabled;
    }

    public function getListFormFilters(array $formFilters): FormInterface
    {
        if (null === $this->listFiltersForm) {
            $formOptions = array();
            if ($this->formCsrfEnabled) {
                $formOptions['csrf_protection'] = false;
            }
            $formBuilder = $this->formFactory->createNamedBuilder(
                'form_filters', FormType::class, null, $formOptions
            );

            foreach ($formFilters as $name => $config) {
                $formBuilder->add(
                    $name,
                    $config['type'] ?? null,
                    \array_merge(
                        array('required' => false),
                        isset($config['type_options']) ? $config['type_options'] : []
                    )
                );
            }

            $this->listFiltersForm = $formBuilder->setMethod('GET')->getForm();
            $this->listFiltersForm->handleRequest($this->requestStack->getCurrentRequest());
        }

        return $this->listFiltersForm;
    }
}
