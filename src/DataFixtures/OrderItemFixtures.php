<?php

namespace App\DataFixtures;

use App\Entity\Order;
use App\Entity\Product;

use App\Entity\OrderItem;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class OrderItemFixtures extends Fixture implements DependentFixtureInterface
{

    public function load(ObjectManager $manager): void
    {
        $item = new OrderItem();
        // Utilise 'order_1' comme créé dans OrderFixtures
        $item->setOrder($this->getReference('order_1', Order::class));
        //  Utilise 'product_big_wacdo' comme créé dans ProductFixtures
        $item->setProduct($this->getReference('product_big_wacdo', Product::class));
        $item->setQuantity(2);
        $item->setUnitPrice('9.50');

        $manager->persist($item);
        $manager->flush();
    }
    public function getDependencies(): array
    {
        return [
            OrderFixtures::class,
            ProductFixtures::class,
        ];
    }
}
