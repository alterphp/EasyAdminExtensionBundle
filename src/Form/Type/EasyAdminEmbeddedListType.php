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
        $view->vars['entity'] = $options['entity'];

        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $filters = array_map(function ($filter) use ($propertyAccessor, $form) {
            if (0 === strpos($filter, 'form:')) {
                $filter = $propertyAccessor->getValue($form, substr($filter, 5));
            }

            return $filter;
        }, $options['filters']);

        $view->vars['filters'] = $filters;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefault('filters', [])
            ->setRequired('entity')
        ;
    }
}
