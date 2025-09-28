<?php

namespace App\DataFixtures;

use App\Entity\Order;
use App\Entity\Product;
use App\Entity\OrderItem;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class OrderFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // Commandes fictives pour démonstration des rôles 
        // absence d'utilisateur externe
        // À supprimer ou ignorer en production réelle.
        // uniquement utiles pour remplissage de la base pour test

        // ------------------------------
        // Commande 1 : En cours
        // ------------------------------
        $order1 = new Order();
        $order1->setUser(null); // pas d'utilisateur réel
        $order1->setTotal('23.60');
        $order1->setStatut(Order::STATUT_EN_COURS);

        // Optionnel : ajouter des items si ProductFixtures
        $item1 = new OrderItem();
        $item1->setProduct($this->getReference(ProductFixtures::BIG_WACDO, Product::class)); // référence produit
        $item1->setQuantity(2);
        $item1->setUnitPrice(9.50);
        $order1->addOrderItem($item1);

        $manager->persist($order1);
        $manager->persist($item1);
        $this->addReference('order_1', $order1);

        // ------------------------------
        // Commande 2 : En préparation
        // ------------------------------
        $order2 = new Order();
        $order2->setUser(null);
        $order2->setTotal('15.40');
        $order2->setStatut(Order::STATUT_EN_PREPARATION);

        $item2 = new OrderItem();
        $item2->setProduct($this->getReference(ProductFixtures::CHEESEBURGER, Product::class));
        $item2->setQuantity(1);
        $item2->setUnitPrice(5.50);
        $order2->addOrderItem($item2);

        $manager->persist($order2);
        $manager->persist($item2);
        $this->addReference('order_2', $order2);

        // ------------------------------
        // Commande 3 : Livrée
        // ------------------------------
        $order3 = new Order();
        $order3->setUser(null);
        $order3->setTotal('3.98');
        $order3->setStatut(Order::STATUT_LIVREE);

        $item3 = new OrderItem();
        $item3->setProduct($this->getReference(ProductFixtures::COLA, Product::class));
        $item3->setQuantity(2);
        $item3->setUnitPrice(1.99);
        $order3->addOrderItem($item3);

        $manager->persist($order3);
        $manager->persist($item3);
        $this->addReference('order_3', $order3);

        // flush final
        $manager->flush();
    }

    public function getDependencies(): array
    {
        // ProductFixtures a été chargée
        return [
            ProductFixtures::class,
        ];
    }
}
