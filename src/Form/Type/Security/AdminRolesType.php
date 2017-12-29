<?php

namespace AlterPHP\EasyAdminExtensionBundle\Form\Type\Security;

use AlterPHP\EasyAdminExtensionBundle\Form\Type\Transformer\RestoreRolesTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for admin roles.
 *
 * Much inspired from SonataUserBundle SecurityRolesType.
 *
 * @see https://github.com/sonata-project/SonataUserBundle/blob/4.x/src/Form/Type/SecurityRolesType.php
 */
class AdminRolesType extends AbstractType
{
    private $rolesBuilder;

    public function __construct($editableRolesBuilder)
    {
        $this->rolesBuilder = $editableRolesBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $formBuilder, array $options): void
    {
        /*
         * The form shows only roles that the current user can edit for the targeted user. Now we still need to persist
         * all other roles. It is not possible to alter those values inside an event listener as the selected
         * key will be validated. So we use a Transformer to alter the value and an listener to catch the original values
         *
         * The transformer will then append non editable roles to the user ...
         */
        $transformer = new RestoreRolesTransformer($this->rolesBuilder);
        // GET METHOD
        $formBuilder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($transformer): void {
            $transformer->setOriginalRoles($event->getData());
        });
        // POST METHOD
        $formBuilder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($transformer): void {
            $transformer->setOriginalRoles($event->getForm()->getData());
        });
        $formBuilder->addModelTransformer($transformer);
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $attr = $view->vars['attr'];
        $view->vars['choice_translation_domain'] = false; // RolesBuilder all ready does translate them
        $view->vars['attr'] = $attr;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(array(
            // make expanded default value
            'expanded' => true,
            'choices' => function (Options $options, $parentChoices) {
                if (!empty($parentChoices)) {
                    return array();
                }
                $roles = $this->rolesBuilder->getRoles($options['expanded']);

                return array_flip($roles);
            },
            'multiple' => true,
            'data_class' => null,
        ));
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}
