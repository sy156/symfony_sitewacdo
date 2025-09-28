<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CatalogueController extends AbstractController
{
    #[Route('/products', name: 'app_products', methods: ['GET'])]
    public function index(ProductRepository $productRepository): Response
    {
        return $this->render('catalogue/index.html.twig', [
            'products' => $productRepository->findAll(),
        ]);
    }

    #[Route('/products/{id}', name: 'app_product_show', methods: ['GET'])]
    public function show(Product $product): Response
    {
        return $this->render('catalogue/show.html.twig', [
            'product' => $product,
        ]);
    }
}
