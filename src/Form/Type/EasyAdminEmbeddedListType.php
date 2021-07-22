<?php

namespace AlterPHP\EasyAdminExtensionBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccess;

class EasyAdminEmbeddedListType extends AbstractType
{
    /**
     * @var \AlterPHP\EasyAdminExtensionBundle\Helper\EmbeddedListHelper
     */
    private $embeddedListHelper;

    /**
     * EasyAdminEmbeddedListType constructor.
     *
     * @param \AlterPHP\EasyAdminExtensionBundle\Helper\EmbeddedListHelper $embeddedListHelper
     */
    public function __construct($embeddedListHelper)
    {
        $this->embeddedListHelper = $embeddedListHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'easyadmin_embedded_list';
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if (null !== $options['document']) {
            $view->vars['object_type'] = 'document';
            $this->buildViewForDocumentList($view, $form, $options);
        } else {
            $view->vars['object_type'] = 'entity';
            $this->buildViewForEntityList($view, $form, $options);
        }

        $view->vars['hidden_fields'] = $options['hidden_fields'];
        $view->vars['max_results'] = $options['max_results'];

        if ($options['sort']) {
            $sort['field'] = $options['sort'][0];
            $sort['direction'] = $options['sort'][1] ?? 'DESC';
            $view->vars['sort'] = $sort;
        }
    }

    private function buildViewForEntityList(FormView $view, FormInterface $form, array $options)
    {
        $parentData = $form->getParent()->getData();

        $embeddedListEntity = $options['entity'];
        $embeddedListFilters = $options['ext_filters'];
        // Guess entity FQCN from parent metadata
        $entityFqcn = $this->embeddedListHelper->getEntityFqcnFromParent(\get_class($parentData), $form->getName());
        if (null !== $entityFqcn) {
            $view->vars['object_fqcn'] = $entityFqcn;
            // Guess embeddedList entity if not set
            if (null === $embeddedListEntity) {
                $embeddedListEntity = $this->embeddedListHelper->guessEntityEntry($entityFqcn);
            }
        }
        $view->vars['entity'] = $embeddedListEntity;
        $view->vars['parent_object_property'] = $form->getConfig()->getName();

        // Only for backward compatibility (when there were no guesser)
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $extFilters = \array_map(function ($filter) use ($propertyAccessor, $form) {
            if (0 === \strpos($filter, 'form:')) {
                $filter = $propertyAccessor->getValue($form, \substr($filter, 5));
            }

            return $filter;
        }, $embeddedListFilters);
        $view->vars['ext_filters'] = $extFilters;
    }

    private function buildViewForDocumentList(FormView $view, FormInterface $form, array $options)
    {
        $parentData = $form->getParent()->getData();

        $view->vars['document'] = $options['document'];
        $embeddedListFilters = $options['ext_filters'];

        // Only for backward compatibility (when there were no guesser)
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $extFilters = \array_map(function ($filter) use ($propertyAccessor, $parentData) {
            if (0 === \strpos($filter, 'form:')) {
                $filter = $propertyAccessor->getValue($parentData, \substr($filter, 5));
            }

            return $filter;
        }, $embeddedListFilters);
        $view->vars['ext_filters'] = $extFilters;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefault('document', null)
            ->setDefault('entity', null)
            ->setDefault('ext_filters', [])
            ->setDefault('hidden_fields', [])
            ->setDefault('max_results', null)
            ->setDefault('sort', null)
            ->setDefault('mapped', false)
            ->setAllowedTypes('document', ['null', 'string'])
            ->setAllowedTypes('entity', ['null', 'string'])
            ->setAllowedTypes('ext_filters', ['array'])
            ->setAllowedTypes('hidden_fields', ['array'])
            ->setAllowedTypes('max_results', ['null', 'int'])
            ->setAllowedTypes('sort', ['null', 'string', 'array'])
        ;
    }
}
