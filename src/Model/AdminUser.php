<?php

namespace AlterPHP\EasyAdminExtensionBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Storage agnostic admin user object.
 */
class AdminUser implements UserInterface
{
    /**
     * @var mixed
     */
    protected $id;

    /**
     * @var string
     */
    protected $email;

    /**
     * @var Collection
     */
    protected $groups;

    /**
     * Display as a string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getUsername();
    }

    /**
     * User constructor.
     */
    public function __construct()
    {
        $this->groups = new ArrayCollection();
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
     * Get email.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Get groups.
     *
     * @return Collection
     */
    public function getGroups(): Collection
    {
        return $this->groups;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles()
    {
        $roles = array('ROLE_ADMIN');

        foreach ($this->getGroups() as $group) {
            $roles = \array_merge($roles, $group->getRoles());
        }

        return \array_values(\array_unique($roles));
    }

    /**
     * {@inheritdoc}
     */
    public function getPassword()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getSalt()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getUsername()
    {
        return $this->email;
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials()
    {
    }
}
