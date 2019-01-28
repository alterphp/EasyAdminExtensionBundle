<?php

namespace AlterPHP\EasyAdminExtensionBundle\Tests\Fixtures\AppTestBundle\DataFixtures;

use AppTestBundle\Entity\FunctionalTests\AdminGroup;
use AppTestBundle\Entity\FunctionalTests\AdminUser;
use AppTestBundle\Entity\FunctionalTests\Category;
use AppTestBundle\Entity\FunctionalTests\Product;
use AppTestBundle\Entity\FunctionalTests\Purchase;
use AppTestBundle\Entity\FunctionalTests\PurchaseItem;
use AppTestBundle\Entity\FunctionalTests\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    private $phrases = [
        'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        'Pellentesque vitae velit ex.',
        'Mauris dapibus, risus quis suscipit vulputate, eros diam egestas libero, eu vulputate eros eros eu risus.',
        'In hac habitasse platea dictumst.',
        'Morbi tempus commodo mattis.',
        'Donec vel elit dui.',
        'Ut suscipit posuere justo at vulputate.',
        'Phasellus id porta orci.',
        'Ut eleifend mauris et risus ultrices egestas.',
        'Aliquam sodales, odio id eleifend tristique, urna nisl sollicitudin urna, id varius orci quam id turpis.',
        'Nulla porta lobortis ligula vel egestas.',
        'Curabitur aliquam euismod dolor non ornare.',
        'Nunc et feugiat lectus.',
        'Nam porta porta augue.',
        'Sed varius a risus eget aliquam.',
        'Nunc viverra elit ac laoreet suscipit.',
        'Pellentesque et sapien pulvinar, consectetur eros ac, vehicula odio.',
    ];

    public function load(ObjectManager $manager)
    {
        $adminGroups = $this->createAdminGroups();
        foreach ($adminGroups as $adminGroup) {
            $manager->persist($adminGroup);
        }

        $adminUsers = $this->createAdminUsers();
        foreach ($adminUsers as $adminUser) {
            $manager->persist($adminUser);
        }

        $users = $this->createUsers();
        foreach ($users as $user) {
            $manager->persist($user);
        }

        $categories = $this->createCategories();
        foreach ($categories as $category) {
            $manager->persist($category);
        }

        $products = $this->createProducts();
        foreach ($products as $product) {
            $manager->persist($product);
        }

        $purchases = $this->createPurchases();
        foreach ($purchases as $purchase) {
            $manager->persist($purchase);
        }

        $purchaseItems = $this->createPurchaseItems();
        foreach ($purchaseItems as $purchaseItem) {
            $manager->persist($purchaseItem);
        }

        $manager->flush();
    }

    private function createAdminGroups(): array
    {
        $adminGroups = [];

        foreach (\range(1, 20) as $i) {
            $adminGroup = new AdminGroup();
            $adminGroup->setName('admin-group'.$i);
            $adminGroup->setRoles(['ROLE_GROUP_'.$i, 'ROLE_GROUP_'.($i + 1)]);

            $this->addReference('admin-group-'.$i, $adminGroup);
            $adminGroups[] = $adminGroup;
        }

        return $adminGroups;
    }

    private function createAdminUsers(): array
    {
        $adminUsers = [];

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
            $adminUsers[] = $adminUser;
        }

        return $adminUsers;
    }

    private function createUsers(): array
    {
        $users = [];

        foreach (\range(1, 20) as $i) {
            $user = new User();
            $user->setUsername('user'.$i);
            $user->setEmail('user'.$i.'@example.com');

            $this->addReference('user-'.$i, $user);
            $users[] = $user;
        }

        return $users;
    }

    private function createCategories(): array
    {
        $parentCategories = [];
        $subCategories = [];

        foreach (\range(1, 100) as $i) {
            $category = new Category();
            $category->setName('Parent Category #'.$i);

            $this->addReference('category-'.$i, $category);

            $parentCategories[] = $category;
        }

        foreach (\range(1, 100) as $i) {
            $category = new Category();
            $category->setName('Category #'.$i);
            $category->setParent($this->getReference('category-'.$i));

            $this->addReference('category-'.(100 + $i), $category);

            $subCategories[] = $category;
        }

        return \array_merge($parentCategories, $subCategories);
    }

    private function createProducts(): array
    {
        $products = [];

        foreach (\range(1, 100) as $i) {
            $category = $i <= 10 ? $this->getReference('category-1') : $this->getReference('category-'.\mt_rand(2, 100));
            $product = new Product();
            $product->setEnabled($i % 10 ? true : false);
            $product->setOddEven($i % 4 ? 'odd' : 'even');
            $product->setReference('ref'.\str_pad($i, 6, '0', STR_PAD_LEFT));
            $product->setName($this->getRandomName());
            $product->setReplenishmentType($this->getReplenishmentType());
            $product->setPrice($i * 100);
            $product->setTags($this->getRandomTags());
            $product->setEan($this->getRandomEan());
            $product->setCategory($category);
            $product->setDescription($this->getRandomDescription());
            $product->setHtmlFeatures($this->getRandomHtmlFeatures());
            $product->setPhone($i <= 10 ? null : '0123456789');

            $this->addReference('product-'.$i, $product);
            $products[] = $product;
        }

        return $products;
    }

    private function createPurchases(): array
    {
        $purchases = [];

        foreach (\range(1, 30) as $i) {
            $purchase = new Purchase();
            $purchase->setGuid($this->generateGuid());
            $purchase->setDeliveryDate(new \DateTime("+$i days"));
            $purchase->setCreatedAt(new \DateTime("now +$i seconds"));
            $purchase->setShipping(new \StdClass());
            $purchase->setDeliveryHour($this->getRandomHour());
            $purchase->setBillingAddress(\json_encode([
                'line1' => '1234 Main Street',
                'line2' => 'Big City, XX 23456',
            ]));
            $purchase->setBuyer($this->getReference('user-'.($i % 20 + 1)));

            $this->addReference('purchase-'.$i, $purchase);
            $purchases[] = $purchase;
        }

        return $purchases;
    }

    private function createPurchaseItems(): array
    {
        $purchaseItems = [];

        foreach (\range(1, 30) as $i) {
            $numItemsPurchased = \rand(1, 5);
            foreach (\range(1, $numItemsPurchased) as $j) {
                $item = new PurchaseItem();
                $item->setQuantity(\rand(1, 3));
                $item->setProduct($this->getRandomProduct());
                $item->setTaxRate(0.21);
                $item->setPurchase($this->getReference('purchase-'.$i));

                $purchaseItems[] = $item;
            }
        }

        return $purchaseItems;
    }

    private function getRandomTags()
    {
        $tags = [
            'books',
            'electronics',
            'GPS',
            'hardware',
            'laptops',
            'monitors',
            'movies',
            'music',
            'printers',
            'smartphones',
            'software',
            'toys',
            'TV & video',
            'videogames',
            'wearables',
        ];

        $numTags = \mt_rand(2, 4);
        \shuffle($tags);

        return \array_slice($tags, 0, $numTags - 1);
    }

    private function getRandomEan()
    {
        $chars = \str_split('0123456789');
        $count = \count($chars) - 1;
        $ean13 = '';
        do {
            $ean13 .= $chars[\mt_rand(0, $count)];
        } while (\strlen($ean13) < 13);

        $checksum = 0;
        foreach (\str_split(\strrev($ean13)) as $pos => $val) {
            $checksum += $val * (3 - 2 * ($pos % 2));
        }
        $checksum = ((10 - ($checksum % 10)) % 10);

        return $ean13.$checksum;
    }

    private function getRandomName()
    {
        $words = [
            'Lorem', 'Ipsum', 'Sit', 'Amet', 'Adipiscing', 'Elit',
            'Vitae', 'Velit', 'Mauris', 'Dapibus', 'Suscipit', 'Vulputate',
            'Eros', 'Diam', 'Egestas', 'Libero', 'Platea', 'Dictumst',
            'Tempus', 'Commodo', 'Mattis', 'Donec', 'Posuere', 'Eleifend',
        ];

        $numWords = 2;
        \shuffle($words);

        return 'Product '.\implode(' ', \array_slice($words, 0, $numWords));
    }

    private function getRandomPrice()
    {
        $cents = ['00', '29', '39', '49', '99'];

        return (float) \mt_rand(2, 79).'.'.$cents[\array_rand($cents)];
    }

    private function getRandomDescription()
    {
        $numPhrases = \mt_rand(5, 10);
        \shuffle($this->phrases);

        return \implode(' ', \array_slice($this->phrases, 0, $numPhrases - 1));
    }

    private function getRandomHtmlFeatures()
    {
        $numFeatures = 2;
        \shuffle($this->phrases);

        return '<ul><li>'.\implode('</li><li>', \array_slice($this->phrases, 0, $numFeatures)).'</li></ul>';
    }

    private function getReplenishmentType()
    {
        $replenishmentTypeValues = Product::getReplenishmentTypeValues();

        return $replenishmentTypeValues[\mt_rand(0, \count($replenishmentTypeValues) - 1)];
    }

    private function generateGuid()
    {
        return \sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
          \mt_rand(0, 0xffff), \mt_rand(0, 0xffff),
          \mt_rand(0, 0xffff),
          \mt_rand(0, 0x0fff) | 0x4000,
          \mt_rand(0, 0x3fff) | 0x8000,
          \mt_rand(0, 0xffff), \mt_rand(0, 0xffff), \mt_rand(0, 0xffff)
        );
    }

    private function getRandomHour()
    {
        $date = new \DateTime();

        return $date->setTime(\rand(0, 23), 0);
    }

    private function getRandomProduct()
    {
        $productId = \rand(1, 100);

        return $this->getReference('product-'.$productId);
    }
}
