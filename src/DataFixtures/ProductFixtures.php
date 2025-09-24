<?php

namespace App\DataFixtures;

use App\Entity\Product;
use App\Entity\Category;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class ProductFixtures extends Fixture implements DependentFixtureInterface
{
    public const BIG_WACDO = 'product_big_wacdo';
    public const COLA = 'product_coca_cola';
    public const FREEZE_WACDO = 'product_freeze_wacdo';
    public const BROWNIE = 'product_brownie';
    public const CHEESEBURGER = 'product_cheeseburger';


    public function load(ObjectManager $manager): void
    {
        $product = new Product();
        $product->setName('Big Wacdo');
        $product->setPrice(6.30); //float
        // UTILISE LA CONSTANTE QUI VAUT 'category_burgers'
        $product->setCategory($this->getReference(CategoryFixtures::BURGERS, Category::class));
        $manager->persist($product);
        $this->addReference(self::BIG_WACDO, $product);

        $product = new Product();
        $product->setName('Cheeseburger');
        $product->setPrice(5.50); // float
        // UTILISE LA CONSTANTE QUI VAUT 'category_burgers'
        $product->setCategory($this->getReference(CategoryFixtures::BURGERS, Category::class));
        $manager->persist($product);
        $this->addReference(self::CHEESEBURGER, $product);


        $product = new Product();
        $product->setName('Cola');
        $product->setPrice(1.99);
        // UTILISE LA CONSTANTE QUI VAUT 'category_boissons'
        $product->setCategory($this->getReference(CategoryFixtures::BOISSONS, Category::class));
        $manager->persist($product);
        $this->addReference(self::COLA, $product);

        $product = new Product();
        $product->setName('Freeze Wacdo');
        $product->setPrice(3.10);
        // UTILISE LA CONSTANTE QUI VAUT 'category_desserts'
        $product->setCategory($this->getReference(CategoryFixtures::DESSERTS, Category::class));
        $manager->persist($product);
        $this->addReference(self::FREEZE_WACDO, $product);

        $product = new Product();
        $product->setName('Brownie');
        $product->setPrice(2.85);
        // UTILISE LA CONSTANTE QUI VAUT 'category_desserts'
        $product->setCategory($this->getReference(CategoryFixtures::DESSERTS, Category::class));
        $manager->persist($product);
        $this->addReference(self::BROWNIE, $product);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            CategoryFixtures::class,
        ];
    }
}
