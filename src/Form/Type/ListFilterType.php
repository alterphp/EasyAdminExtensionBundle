<?php


namespace AlterPHP\EasyAdminExtensionBundle\Form\Type;

use AlterPHP\EasyAdminExtensionBundle\Model\ListFilter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ListFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('value', $options['input_type'], $options['input_type_options'] + [
                'label' => false,
                'required' => false
            ])
        ;

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($options) {
            $listFilter = $event->getData();

            $listFilter->setOperator($options['operator']);
            if (isset($options['property']) && !empty($options['property'])) {
                $listFilter->setProperty($options['property']);
            }
        });
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'operator' => ListFilter::OPERATOR_EQUALS,
            'data_class' => ListFilter::class,
            'input_type' =>  TextType::class,
            'input_type_options' => [],
        ));
        $resolver->setDefined(['property']);
        $resolver->setAllowedValues('operator', ListFilter::getOperatorsList());
    }
}
