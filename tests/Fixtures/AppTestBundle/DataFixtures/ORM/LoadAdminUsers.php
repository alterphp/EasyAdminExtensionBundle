<?php

namespace AlterPHP\EasyAdminExtensionBundle\Tests\Fixtures\AppTestBundle\DataFixtures\ORM;

use AppTestBundle\Entity\FunctionalTests\AdminUser;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadAdminUsers extends AbstractFixture implements OrderedFixtureInterface
{
    public function getOrder()
    {
        return 11;
    }

    public function load(ObjectManager $manager)
    {
        foreach (\range(1, 20) as $i) {
            $adminUser = new AdminUser();
            $adminUser->updateFromData([
                'gSuiteId' => 'g-suite-id-'.$i,
                'email' => 'email-'.$i.'@easyadmin.com',
                'lastname' => 'lastname-'.$i,
                'firstname' => 'firstname-'.$i,
            ]);

            // All users are members of first AdminGroup
            $adminUser->addGroup($this->getReference('admin-group-1'));

            $this->addReference('admin-user-'.$i, $adminUser);
            $manager->persist($adminUser);
        }

        $manager->flush();
    }
}
