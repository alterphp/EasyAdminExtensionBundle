<?php

namespace AlterPHP\EasyAdminExtensionBundle\Form\Type\Extension;

use EasyCorp\Bundle\EasyAdminBundle\Form\Type\EasyAdminAutocompleteType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;

class EasyAdminAutocompleteTypeExtension extends AbstractTypeExtension
{
    private $router;
    private $translator;

    public function __construct(RouterInterface $router, TranslatorInterface $translator)
    {
        $this->router = $router;
        $this->translator = $translator;
    }

    public function getExtendedType()
    {
        return EasyAdminAutocompleteType::class;
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if (isset($options['attr']['create']) && $options['attr']['create']) {
            $view->vars['attr']['data-easyadmin-autocomplete-create-action-url'] = $this->router->generate(
                'easyadmin', array('action' => 'newAjax', 'entity' => $view->vars['autocomplete_entity_name'])
            );
            $view->vars['attr']['data-easyadmin-autocomplete-create-field-name'] = $view->vars['name'];
            $view->vars['attr']['data-easyadmin-autocomplete-create-button-text'] = $this->translator->trans(
                'action.add_new_item', array(), 'EasyAdminBundle'
            );

            unset($view->vars['attr']['create']);
        }
    }
}
