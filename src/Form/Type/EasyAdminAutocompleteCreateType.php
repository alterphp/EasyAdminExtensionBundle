<?php

namespace AlterPHP\EasyAdminExtensionBundle\Form\Type;

// use EasyCorp\Bundle\EasyAdminBundle\Configuration\ConfigManager;

// use Symfony\Component\Form\FormBuilderInterface;
// use Symfony\Component\Form\FormInterface;
// use Symfony\Component\Form\FormView;
// use Symfony\Component\OptionsResolver\OptionsResolver;

use EasyCorp\Bundle\EasyAdminBundle\Form\Type\EasyAdminAutocompleteType;

/**
 * Autocomplete form type add new entity.
 *
 * @author Gonzalo Alonso <gonkpo@gmail.com>
 */
class EasyAdminAutocompleteCreateType extends EasyAdminAutocompleteType
{
    // private $configManager;

    // public function __construct(ConfigManager $configManager)
    // {
    //     parent::__construct($configManager);
    // }

    // /**
    //  * {@inheritdoc}
    //  */
    // public function buildForm(FormBuilderInterface $builder, array $options)
    // {
    //     parent::buildForm($builder, $options);
    // }

    // /**
    //  * {@inheritdoc}
    //  */
    // public function buildView(FormView $view, FormInterface $form, array $options)
    // {
    //     parent::buildView($view, $form, $options);
    // }

    // /**
    //  * {@inheritdoc}
    //  */
    // public function finishView(FormView $view, FormInterface $form, array $options)
    // {
    //     parent::buildView($view, $form, $options);
    // }

    // /**
    //  * {@inheritdoc}
    //  */
    // public function configureOptions(OptionsResolver $resolver)
    // {
    //     parent::configureOptions($resolver);
    // }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'easyadmin_autocomplete_create';
    }

    // /**
    //  * {@inheritdoc}
    //  */
    // public function getName()
    // {
    //     return $this->getBlockPrefix();
    // }
}
