<?php
// 1. CategoryFixtures.php (à créer si elle n'existe pas déjà)

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CategoryFixtures extends Fixture
{
    public const BURGERS = 'category_burgers';
    public const BOISSONS = 'category_boissons';
    public const DESSERTS = 'category_desserts';

    public function load(ObjectManager $manager): void
    {
        // Créer la catégorie Burgers
        $burgers = new Category();
        $burgers->setName('Burgers');
        $manager->persist($burgers);
        $this->addReference(self::BURGERS, $burgers);

        // Créer la catégorie Boissons
        $boissons = new Category();
        $boissons->setName('Boissons');
        $manager->persist($boissons);
        $this->addReference(self::BOISSONS, $boissons);

        // Créer la catégorie Desserts
        $desserts = new Category();
        $desserts->setName('Desserts');
        $manager->persist($desserts);
        $this->addReference(self::DESSERTS, $desserts);

        $manager->flush();
    }
}
