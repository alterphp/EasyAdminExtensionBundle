<?php

namespace AlterPHP\EasyAdminExtensionBundle\Model;

/**
 * Storage agnostic admin group object.
 */
class AdminGroup
{
    /**
     * @var mixed
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $roles;

    /**
     * Display as a string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }

    /**
     * Group constructor.
     */
    public function __construct()
    {
        $this->roles = array();
    }

    /**
     * Get id.
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return static
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set roles.
     *
     * @param array $roles
     *
     * @return static
     */
    public function setRoles(array $roles)
    {
        $this->roles = array_unique($roles);

        return $this;
    }

    /**
     * Get roles.
     *
     * @return array
     */
    public function getRoles(): array
    {
        return array_filter($this->roles);
    }
}
