<?php

namespace AlterPHP\EasyAdminExtensionBundle\Form\Transformer\Operator\Traits;

trait OperatorModelTransformerTrait
{
    public function transform($value)
    {
        if (strpos($value, static::OPERATOR_PREFIX) !== 0) {
            // remove prefix if set
            $value = substr($value, \strlen(static::OPERATOR_PREFIX)) ;
        }

        return $value;
    }

    public function reverseTransform($value)
    {
        if (strpos($value, static::OPERATOR_PREFIX) !== 0) {
            // add prefix if not set
            $value = static::OPERATOR_PREFIX . $value;
        }

        return $value;
    }

}
