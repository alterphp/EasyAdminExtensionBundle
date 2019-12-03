<?php

namespace AlterPHP\EasyAdminExtensionBundle\Model;

class ListFilter
{
    /** @var string */
    protected $operator;

    /** @var mixed */
    protected $value;

    /**
     * @var string
     *
     * Used to override the property, allowing multiple list filter on the same property
     */
    protected $property;

    public const OPERATOR_EQUALS = 'equals';
    public const OPERATOR_NOT = 'not';
    public const OPERATOR_IN = 'in';
    public const OPERATOR_NOTIN = 'notin';
    public const OPERATOR_GT = 'gt';
    public const OPERATOR_GTE = 'gte';
    public const OPERATOR_LT = 'lt';
    public const OPERATOR_LTE = 'lte';
    public const OPERATOR_LIKE = 'like';

    private static $operatorValues = null;

    final public function __construct()
    {
    }

    public static function createFromRequest(string $property, string $operator, $value)
    {
        $listFilter = new static();

        $listFilter
            ->setProperty($property)
            ->setOperator($operator)
            ->setValue($value)
        ;

        return $listFilter;
    }

    /**
     * Returns operators list.
     *
     * @return array
     */
    public static function getOperatorsList()
    {
        // Build $operatorValues if this is the first call
        if (null === static::$operatorValues) {
            static::$operatorValues = [];
            $refClass = new \ReflectionClass(static::class);
            $classConstants = $refClass->getConstants();

            $constantPrefix = 'OPERATOR_';
            foreach ($classConstants as $key => $val) {
                if (\substr($key, 0, \strlen($constantPrefix)) === $constantPrefix) {
                    static::$operatorValues[] = $val;
                }
            }
        }

        return static::$operatorValues;
    }

    public function getOperator()
    {
        return $this->operator;
    }

    public function setOperator(string $operator)
    {
        if (!\in_array($operator, static::getOperatorsList())) {
            throw new \InvalidArgumentException(\sprintf('Operator "%s" is not allowed !', $operator));
        }

        $this->operator = $operator;

        return $this;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    public function hasProperty()
    {
        return !empty($this->property);
    }

    public function getProperty()
    {
        return $this->property;
    }

    public function setProperty($property)
    {
        $this->property = $property;

        return $this;
    }
}
