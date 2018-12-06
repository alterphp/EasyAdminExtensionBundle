<?php

namespace AlterPHP\EasyAdminExtensionBundle\Form\Transformer\Operator;

use AlterPHP\EasyAdminExtensionBundle\Form\Transformer\Operator\Traits\OperatorModelTransformerTrait;
use Symfony\Component\Form\DataTransformerInterface;

class GreaterThanModelTransformer implements DataTransformerInterface
{
    const OPERATOR_PREFIX = "_GT";

    use OperatorModelTransformerTrait;
}