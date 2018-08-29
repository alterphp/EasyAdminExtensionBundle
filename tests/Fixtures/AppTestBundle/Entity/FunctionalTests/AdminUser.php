<?php

namespace AppTestBundle\Entity\FunctionalTests;

use AlterPHP\EasyAdminExtensionBundle\Model\AdminUser as BaseAdminUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class AdminUser extends BaseAdminUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", unique=true)
     */
    private $gSuiteId;

    /**
     * @ORM\Column(type="string")
     */
    private $lastname;

    /**
     * @ORM\Column(type="string")
     */
    private $firstname;

    /**
     * @ORM\ManyToMany(targetEntity="AppTestBundle\Entity\FunctionalTests\AdminGroup", inversedBy="users", fetch="EAGER")
     * @ORM\JoinTable(name="admin_user_group",
     *      joinColumns={@ORM\JoinColumn(name="admin_user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="admin_group_id", referencedColumnName="id")}
     * )
     */
    protected $groups;

    public function updateFromData(array $data = [])
    {
        if (isset($data['gSuiteId'])) {
            $this->gSuiteId = $data['gSuiteId'];
        }
        if (isset($data['email'])) {
            $this->email = $data['email'];
        }
        if (isset($data['lastname'])) {
            $this->lastname = $data['lastname'];
        }
        if (isset($data['firstname'])) {
            $this->firstname = $data['firstname'];
        }
    }

    public function getGSuiteId()
    {
        return $this->gSuiteId;
    }

    public function getLastname()
    {
        return $this->lastname;
    }

    public function getFirstname()
    {
        return $this->firstname;
    }

    public function addGroup(AdminGroup $adminGroup)
    {
        if (!$this->groups->contains($adminGroup)) {
            $this->groups->add($adminGroup);
        }

        return $this;
    }
}
