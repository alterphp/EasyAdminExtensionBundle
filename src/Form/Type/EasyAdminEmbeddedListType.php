<?php

namespace AlterPHP\EasyAdminExtensionBundle\Form\Type;

use AlterPHP\EasyAdminExtensionBundle\EventListener\EmbeddedListTypeGuesserSubscriber;
use Doctrine\ORM\PersistentCollection as OrmPersistentCollection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
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
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {

        });
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

        $embeddedListEntity = $options['entity'];
        $embeddedListFilters = $options['filters'];

        if ($data instanceof OrmPersistentCollection) {

            $entityFqcn = $data->getTypeClass()->getName();

            // Guess embeddedList entity if not set
            if (!isset($embeddedListEntity)) {
                $embeddedListEntity = $this->embeddedListHelper->guessEntityEntry($entityFqcn);
            }

            // Guess default filter and let it be overriden by defined filters
            $embeddedListFilters = array_merge(
                $this->embeddedListHelper->guessDefaultFilter(
                    $entityFqcn, $form->getConfig()->getName(), $data->getOwner()
                ),
                $embeddedListFilters
            );
        }

        $view->vars['entity'] = $embeddedListEntity;

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
