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
        $operator = $options['operator'];
        if (!array_key_exists($operator, static::$operators)) {
            throw new \InvalidArgumentException('operator should be one of ' . implode(', ', array_keys(static::$operators)) . ". $operator passed");
        }

        $property = $options['property'];
        $type = $options['input_type'];
        unset($options['operator'], $options['input_type'], $options['property']);

        $builder
            ->add('value', $type, $options['input_type_options'] + [
                'label' => false,
                'required' => false
            ])
            ->add('operator', HiddenType::class, [
                'data' => self::$operators[$operator]
            ]);
        if ($property !== null) {
            $builder
                ->add('property', HiddenType::class, [
                    'data' => $property
                ]);
        }
        
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'operator' => 'equals',
            'data_class' => ListFilter::class,
            'input_type' =>  TextType::class,
            'input_type_options' => []
        ));
        $resolver->setDefined([
            'property',
        ]);
    }
}
