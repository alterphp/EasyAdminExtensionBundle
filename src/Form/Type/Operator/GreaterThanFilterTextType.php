<?php

namespace AlterPHP\EasyAdminExtensionBundle\Form\Type\Operator;

use AlterPHP\EasyAdminExtensionBundle\Form\Transformer\Operator\GreaterThanModelTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class GreaterThanFilterTextType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer(new GreaterThanModelTransformer());
    }


    public function getParent()
    {
        return TextType::class;
    }
}
