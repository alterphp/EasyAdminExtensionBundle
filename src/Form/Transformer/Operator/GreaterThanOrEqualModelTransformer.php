<?php

namespace AlterPHP\EasyAdminExtensionBundle\Form\Transformer\Operator;

use AlterPHP\EasyAdminExtensionBundle\Form\Transformer\Operator\Traits\OperatorModelTransformerTrait;
use Symfony\Component\Form\DataTransformerInterface;

class GreaterThanOrEqualModelTransformer implements DataTransformerInterface
{
    const OPERATOR_PREFIX = '_GTE';

    use OperatorModelTransformerTrait;
}
