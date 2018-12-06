<?php

namespace AlterPHP\EasyAdminExtensionBundle\Form\Transformer\Operator;

use AlterPHP\EasyAdminExtensionBundle\Form\Transformer\Operator\Traits\OperatorModelTransformerTrait;
use Symfony\Component\Form\DataTransformerInterface;

class LowerThanModelTransformer implements DataTransformerInterface
{
    const OPERATOR_PREFIX = "_LT";

    use OperatorModelTransformerTrait;
}
