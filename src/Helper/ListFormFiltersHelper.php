<?php

declare(strict_types=1);

namespace AlterPHP\EasyAdminExtensionBundle\Helper;

use AlterPHP\EasyAdminExtensionBundle\Form\Type\ListFilterType;
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
            $formOptions = [];
            if ($this->formCsrfEnabled) {
                $formOptions['csrf_protection'] = false;
            }
            $formBuilder = $this->formFactory->createNamedBuilder(
                'form_filters', FormType::class, null, $formOptions
            );


            foreach ($formFilters as $name => $config) {
                $listFilterformOptions = [
                    'label' => $config['label'] ?? null,
                    'required' => false,
                    'input_type' => $config['type'],
                    'input_type_options' => $config['type_options'] ?? [],
                ];
                if (isset($config['operator'])) {
                    $listFilterformOptions['operator'] = $config['operator'];
                }
                if (isset($config['property'])) {
                    $listFilterformOptions['property'] = $config['property'];
                }

                $formBuilder->add($name, ListFilterType::class, $listFilterformOptions);
            }

            $this->listFiltersForm = $formBuilder->setMethod('GET')->getForm();
            $this->listFiltersForm->handleRequest($this->requestStack->getCurrentRequest());
        }

        return $this->listFiltersForm;
    }
}
