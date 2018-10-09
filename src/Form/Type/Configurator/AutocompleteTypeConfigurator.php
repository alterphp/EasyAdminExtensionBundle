<?php

namespace AlterPHP\EasyAdminExtensionBundle\Form\Type\Configurator;

use EasyCorp\Bundle\EasyAdminBundle\Form\Type\Configurator\AutocompleteTypeConfigurator AS BaseAutocompleteTypeConfigurator;
use Symfony\Component\Form\FormConfigInterface;

/**
 * @author Gonzalo Alonso <gonkpo@gmail.com>
 */
class AutocompleteTypeConfigurator extends BaseAutocompleteTypeConfigurator
{
    /**
     * {@inheritdoc}
     */
    public function configure($name, array $options, array $metadata, FormConfigInterface $parentConfig)
    {
        $options = parent::configure($name, $options, $metadata, $parentConfig);
        // by default, attr create = false
        if (isset($options['attr']['create'])) {
            $options['attr']['create'] = $options['attr']['create'];
        } else {
            $options['attr']['create'] = false;
        }

        return $options;
    }
}
