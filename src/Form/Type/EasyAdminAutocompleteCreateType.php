<?php

namespace AlterPHP\EasyAdminExtensionBundle\Form\Type;

use EasyCorp\Bundle\EasyAdminBundle\Form\Type\EasyAdminAutocompleteType;

/**
 * Autocomplete form type add new entity.
 *
 * @author Gonzalo Alonso <gonkpo@gmail.com>
 */
class EasyAdminAutocompleteCreateType extends EasyAdminAutocompleteType
{
    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'easyadmin_autocomplete_create';
    }
}
