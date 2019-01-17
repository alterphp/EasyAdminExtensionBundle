<?php


namespace AlterPHP\EasyAdminExtensionBundle\Form\Type;


use AlterPHP\EasyAdminExtensionBundle\Form\Transformer\Operator\GreaterThanOrEqualModelTransformer;
use AlterPHP\EasyAdminExtensionBundle\Model\ListFilter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ListFilterType extends AbstractType
{
    public static $operators = [
        'equals' => '=',
        'gt' => '>',
        'gte' => '>=',
        'lt' => '<',
        'lte' => '<=',
    ];

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('value', $options['input_type'], $options['input_type_options'] + [
                'label' => false,
                'required' => false
            ])
            ->add('operator', HiddenType::class, [
                'data' => self::$operators[$options['operator']]
            ]);
        if (isset($options['property'])) {
            $builder
                ->add('property', HiddenType::class, [
                    'data' => $options['property']
                ]);
        }
        
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'operator' => 'equals',
            'data_class' => ListFilter::class,
            'input_type' =>  TextType::class,
            'input_type_options' => [],
        ));
        $resolver->setDefined([
            'property',
        ]);
        $resolver->setAllowedValues('operator', array_keys(static::$operators));
    }
}
