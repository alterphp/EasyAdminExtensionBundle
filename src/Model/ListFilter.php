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

    public function getOperator()
    {
        return $this->operator;
    }

    public function setOperator(string $operator)
    {
        $this->operator = $operator;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function getProperty()
    {
        return $this->property;
    }

    public function setProperty($property)
    {
        $this->property = $property;
    }


}
