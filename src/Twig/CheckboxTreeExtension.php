<?php

namespace AlterPHP\EasyAdminExtensionBundle\Twig;

use Symfony\Component\Form\FormView;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CheckboxTreeExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'checkbox_tree',
                [$this, 'renderCheckboxTree'],
                ['is_safe' => ['html'], 'needs_environment' => true]
            ),
            new TwigFunction('pick_checkbox_form', [$this, 'pickCheckboxForm']),
        ];
    }

    public function renderCheckboxTree(Environment $env, FormView $checkboxGroup)
    {
        return $env->render('@EasyAdmin/form/checkbox_tree.html.twig', ['checkboxGroup' => $checkboxGroup]);
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
