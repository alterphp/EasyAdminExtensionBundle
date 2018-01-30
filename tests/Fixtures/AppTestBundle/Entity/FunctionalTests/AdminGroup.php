<?php

namespace AppTestBundle\Entity\FunctionalTests;

use AlterPHP\EasyAdminExtensionBundle\Model\AdminGroup as BaseAdminGroup;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class AdminGroup extends BaseAdminGroup
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToMany(targetEntity="AppTestBundle\Entity\FunctionalTests\AdminUser", mappedBy="groups", fetch="EAGER")
     */
    protected $users;

    public function setUsers(ArrayCollection $users)
    {
        $this->users = $users;

        return $this;
    }

    public function getUsers()
    {
        return $this->users;
    }
}
