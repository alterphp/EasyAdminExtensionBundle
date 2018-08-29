<?php

namespace AlterPHP\EasyAdminExtensionBundle\Tests\Fixtures\AppTestBundle\DataFixtures\ORM;

use AppTestBundle\Entity\FunctionalTests\AdminGroup;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadAdminGroups extends AbstractFixture implements OrderedFixtureInterface
{
    public function getOrder()
    {
        return 10;
    }

    public function load(ObjectManager $manager)
    {
        foreach (range(1, 20) as $i) {
            $adminGroup = new AdminGroup();
            $adminGroup->setName('admin-group'.$i);
            $adminGroup->setRoles(array('ROLE_GROUP_'.$i, 'ROLE_GROUP_'.($i + 1)));

            $this->addReference('admin-group-'.$i, $adminGroup);
            $manager->persist($adminGroup);
        }

        $manager->flush();
    }
}
