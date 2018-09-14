<?php

namespace AlterPHP\EasyAdminExtensionBundle\Form\Type\Configurator;

use EasyCorp\Bundle\EasyAdminBundle\Form\Type\Configurator\AutocompleteTypeConfigurator;

/**
 * @author Gonzalo Alonso <gonkpo@gmail.com>
 */
class AutocompleteCreateTypeConfigurator extends AutocompleteTypeConfigurator
{
    /**
     * {@inheritdoc}
     */
    public function supports($type, array $options, array $metadata)
    {
        $supportedTypes = array(
            'autocomplete_create',
            'AlterPHP\EasyAdminExtensionBundle\Form\Type\EasyAdminAutocompleteCreateType'
        );

        return in_array($type, $supportedTypes, true);
    }
}
