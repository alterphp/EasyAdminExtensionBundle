<?php

namespace AlterPHP\EasyAdminExtensionBundle\Form\Transformer\Operator;

use AlterPHP\EasyAdminExtensionBundle\Form\Transformer\Operator\Traits\OperatorModelTransformerTrait;
use Symfony\Component\Form\DataTransformerInterface;

class LowerThanOrEqualModelTransformer implements DataTransformerInterface
{
    const OPERATOR_PREFIX = "_LTE";

    use OperatorModelTransformerTrait;
}
