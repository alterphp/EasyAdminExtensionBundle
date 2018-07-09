<?php

namespace AlterPHP\EasyAdminExtensionBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccess;

class EasyAdminEmbeddedListType extends AbstractType
{
    private $embeddedListHelper;

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
        $data = $form->getData();
        $parentData = $form->getParent()->getData();

        $defaultFilters = [];
        $embeddedListEntity = $options['entity'];
        $embeddedListFilters = $options['filters'];

        // Guess entity FQCN from parent metadata
        $entityFqcn = $this->embeddedListHelper->getEntityFqcnFromParent(get_class($parentData), $form->getName());

        if (null !== $entityFqcn) {
            // Guess embeddedList entity if not set
            if (!isset($embeddedListEntity)) {
                $embeddedListEntity = $this->embeddedListHelper->guessEntityEntry($entityFqcn);
            }
            // Guess default filters
            $defaultFilters = $this->embeddedListHelper->guessDefaultFilter(
                $entityFqcn, $form->getConfig()->getName(), $parentData
            );
        }

        // TODO: Move the default filters guess to the template (as for SHOW view)
        // Let default filters be overriden by defined filters
        $embeddedListFilters = array_merge($defaultFilters, $embeddedListFilters);

        $view->vars['entity'] = $embeddedListEntity;

        // XXX : Only for backward compatibility (when there were no guesser)
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $filters = array_map(function ($filter) use ($propertyAccessor, $form) {
            if (0 === strpos($filter, 'form:')) {
                $filter = $propertyAccessor->getValue($form, substr($filter, 5));
            }

            return $filter;
        }, $embeddedListFilters);

        $view->vars['filters'] = $filters;

        if ($options['sort']) {
            $sort['field'] = $options['sort'][0];
            $sort['direction'] = $options['sort'][1] ?? 'DESC';
            $view->vars['sort'] = $sort;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefault('entity', null)
            ->setDefault('filters', array())
            ->setDefault('sort', null)
            ->setAllowedTypes('entity', ['null', 'string'])
            ->setAllowedTypes('filters', ['array'])
            ->setAllowedTypes('sort', ['null', 'string', 'array'])
        ;
    }
}
