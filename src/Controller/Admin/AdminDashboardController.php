<?php

namespace App\Controller\Admin;

use App\Repository\UserRepository;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminDashboardController extends AbstractController
{
    #[Route('/admin/dashboard', name: 'app_admin_dashboard')]
    public function dashboard(
        UserRepository $userRepo,
        ProductRepository $productRepo,
        CategoryRepository $categoryRepo,
        OrderRepository $orderRepo
    ): Response {
        // Récupération des compteurs
        $usersCount = $userRepo->count([]);
        $productsCount = $productRepo->count([]);
        $categoriesCount = $categoryRepo->count([]);
        $ordersCount = $orderRepo->count([]);

        return $this->render('admin/dashboard.html.twig', [
            'usersCount' => $usersCount,
            'productsCount' => $productsCount,
            'categoriesCount' => $categoriesCount,
            'ordersCount' => $ordersCount,
        ]);
    }
}
