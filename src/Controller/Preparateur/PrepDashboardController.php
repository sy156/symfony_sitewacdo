<?php

namespace App\Controller\Preparateur;

use App\Entity\Order;
use DateTimeImmutable;
use App\Entity\OrderItem;
use App\Form\OrderItemType;
use App\Form\OrderStatutType;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/preparateur', name: 'preparateur_')]
class PrepDashboardController extends AbstractController
{
    // Tableau de bord
    #[Route('/', name: 'dashboard')]
    public function index(): Response
    {
        return $this->render('preparateur/dashboard.html.twig', [
            'title' => 'Tableau de bord Préparateur',
        ]);
    }

    // Liste des commandes
    #[Route('/orders', name: 'order_list')]
    public function listOrders(OrderRepository $orderRepository): Response
    {
        $orders = $orderRepository->findAll();

        return $this->render('preparateur/orders.html.twig', [
            'orders' => $orders,
        ]);
    }

    // Créer une nouvelle commande fictive
    #[Route('/orders/new', name: 'order_new')]
    public function newOrder(EntityManagerInterface $em): Response
    {
        $order = new Order();
        $order->setStatut(Order::STATUT_EN_COURS);
        $order->setCreatedAt(new DateTimeImmutable());
        $order->setTotal('0.00'); // obligé de donner une valeur a 0 sinon crash avec bdd ou total est défini en not null
        $em->persist($order);
        $em->flush();

        return $this->redirectToRoute('preparateur_order_show', ['id' => $order->getId()]);
    }

    // Afficher le détail d'une commande + formulaire items/statut
    #[Route('/orders/{id}', name: 'order_show')]
    public function showOrder(Order $order, Request $request, EntityManagerInterface $em): Response
    {
        // Formulaire pour ajouter un item
        $orderItem = new OrderItem();
        $orderItem->setOrder($order);
        $itemForm = $this->createForm(OrderItemType::class, $orderItem);
        $itemForm->handleRequest($request);

        if ($itemForm->isSubmitted() && $itemForm->isValid()) {
            $em->persist($orderItem);
            $em->flush();
            $this->addFlash('success', 'Item ajouté à la commande');
            return $this->redirectToRoute('preparateur_order_show', ['id' => $order->getId()]);
        }

        // Formulaire pour changer le statut
        $statutForm = $this->createForm(OrderStatutType::class, $order);
        $statutForm->handleRequest($request);

        if ($statutForm->isSubmitted() && $statutForm->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Statut mis à jour');
            return $this->redirectToRoute('preparateur_order_show', ['id' => $order->getId()]);
        }

        return $this->render('preparateur/order_show.html.twig', [
            'order' => $order,
            'itemForm' => $itemForm->createView(),
            'statutForm' => $statutForm->createView(),
        ]);
    }

    // Mettre une commande en préparation
    #[Route('/orders/{id}/prepare', name: 'order_prepare', methods: ['POST'])]
    public function prepareOrder(Order $order, Request $request, EntityManagerInterface $em): RedirectResponse
    {
        if (!$this->isCsrfTokenValid('prepare' . $order->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

        try {
            $order->marquerEnPreparation();
            $em->flush();
            $this->addFlash('success', 'Commande mise en préparation');
        } catch (\LogicException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('preparateur_order_list');
    }

    // Livrer une commande
    #[Route('/orders/{id}/deliver', name: 'order_deliver', methods: ['POST'])]
    public function deliverOrder(Order $order, Request $request, EntityManagerInterface $em): RedirectResponse
    {
        if (!$this->isCsrfTokenValid('deliver' . $order->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

        try {
            $order->marquerLivree();
            $em->flush();
            $this->addFlash('success', 'Commande livrée');
        } catch (\LogicException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('preparateur_order_list');
    }


    // Annuler une commande
    #[Route('/orders/{id}/cancel', name: 'order_cancel', methods: ['POST'])]
    public function cancelOrder(Order $order, Request $request, EntityManagerInterface $em): RedirectResponse
    {
        if (!$this->isCsrfTokenValid('cancel' . $order->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

        try {
            $order->annuler();
            $em->flush();
            $this->addFlash('success', 'Commande annulée');
        } catch (\LogicException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('preparateur_order_list');
    }

    #[Route('orders/{id}/delete', name: 'order_delete')]
    public function delete(Order $order, EntityManagerInterface $em): RedirectResponse

    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN'); // empêche le préparateur d'accéder à la suppression

        foreach ($order->getOrderItems() as $item) {
            $em->remove($item);
        }

        $em->remove($order);
        $em->flush();

        $this->addFlash('success', 'Commande et ses items supprimés avec succès.');

        return $this->redirectToRoute('preparateur_order_list');
    }



    // Liste des produits
    #[Route('/products', name: 'product_list')]
    public function productList(ProductRepository $productRepository): Response
    {
        $products = $productRepository->findAll();

        return $this->render('preparateur/products.html.twig', [
            'products' => $products,
            'title' => 'Produits',
        ]);
    }
}
