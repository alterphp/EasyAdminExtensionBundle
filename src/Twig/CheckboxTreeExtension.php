<?php

namespace AlterPHP\EasyAdminExtensionBundle\Twig;

use Symfony\Component\Form\FormView;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CheckboxTreeExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return array(
            new TwigFunction(
                'checkbox_tree',
                array($this, 'renderCheckboxTree'),
                array('is_safe' => array('html'), 'needs_environment' => true)
            ),
            new TwigFunction('pick_checkbox_form', array($this, 'pickCheckboxForm')),
        );
    }

    public function renderCheckboxTree(Environment $env, FormView $checkboxGroup)
    {
        return $env->render('@EasyAdmin/form/checkbox_tree.html.twig', array('checkboxGroup' => $checkboxGroup));
    }

    public function pickCheckboxForm(array $checkboxList, string $value)
    {
        $checkbox = null;

        foreach ($checkboxList as $key => $checkboxForm) {
            if ($value === $checkboxForm->vars['value']) {
                $checkbox = $checkboxForm;
                break;
            }
        }

        return $checkbox;
    }
}
