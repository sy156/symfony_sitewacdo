<?php

namespace App\Controller\Accueil;

use App\Entity\Order;
use App\Entity\Product;
use App\Entity\OrderItem;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/accueil', name: 'accueil_')]
class AccueilDashboardController extends AbstractController
{
    #[Route('/', name: 'dashboard')]
    public function index(OrderRepository $orderRepo): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ACCUEIL');

        $ordersEnCours = $orderRepo->findBy(['statut' => Order::STATUT_EN_COURS]);
        $ordersToDeliver = $orderRepo->findBy(['statut' => Order::STATUT_EN_PREPARATION]);
        $ordersDelivered = $orderRepo->findBy(['statut' => Order::STATUT_LIVREE]);

        return $this->render('accueil/dashboard.html.twig', [
            'title' => 'Tableau de bord Accueil',
            'ordersEnCours' => $ordersEnCours,
            'ordersToDeliver' => $ordersToDeliver,
            'ordersDelivered' => $ordersDelivered,
        ]);
    }

    #[Route('/orders/new', name: 'order_new', methods: ['POST'])]
    public function newOrder(Request $request, EntityManagerInterface $em): RedirectResponse
    {
        if (!$this->isCsrfTokenValid('order_new', $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

        $order = new Order();
        $order->setStatut(Order::STATUT_EN_COURS);
        $order->setTotal(0.0);
        $order->setCreatedAt(new \DateTimeImmutable());

        $em->persist($order);
        $em->flush();

        $this->addFlash('success', 'Nouvelle commande créée.');

        return $this->redirectToRoute('accueil_order_show', ['id' => $order->getId()]);
    }

    #[Route('/orders/{id}', name: 'order_show')]
    public function showOrder(Order $order, ProductRepository $productRepo): Response
    {
        $products = $productRepo->findAll();

        return $this->render('accueil/order_show.html.twig', [
            'order' => $order,
            'products' => $products,
        ]);
    }

    #[Route('/orders/{orderId}/add-item/{productId}', name: 'order_add_item', methods: ['POST'])]
    public function addItem(int $orderId, int $productId, Request $request, EntityManagerInterface $em): RedirectResponse
    {
        if (!$this->isCsrfTokenValid('add_item' . $orderId, $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

        $order = $em->getRepository(Order::class)->find($orderId);
        $product = $em->getRepository(Product::class)->find($productId);

        if (!$order || !$product) {
            $this->addFlash('error', 'Commande ou produit introuvable.');
            return $this->redirectToRoute('accueil_dashboard');
        }

        $existingItem = null;
        foreach ($order->getItems() as $item) {
            if ($item->getProduct()->getId() === $product->getId()) {
                $existingItem = $item;
                break;
            }
        }

        if ($existingItem) {
            $existingItem->setQuantity($existingItem->getQuantity() + 1);
        } else {
            $orderItem = new OrderItem();
            $orderItem->setOrder($order);
            $orderItem->setProduct($product);
            $orderItem->setQuantity(1);
            $orderItem->setUnitPrice($product->getPrice());
            $order->addOrderItem($orderItem);
            $em->persist($orderItem);
        }

        $total = 0;
        foreach ($order->getItems() as $item) {
            $total += $item->getQuantity() * $item->getUnitPrice();
        }
        $order->setTotal(number_format($total, 2, '.', ''));

        $em->flush();
        $this->addFlash('success', $product->getName() . ' ajouté à la commande.');

        return $this->redirectToRoute('accueil_order_show', ['id' => $order->getId()]);
    }

    #[Route('/orders/{orderId}/remove-item/{itemId}', name: 'order_remove_item', methods: ['POST'])]
    public function removeItem(int $orderId, int $itemId, Request $request, EntityManagerInterface $em): RedirectResponse
    {
        if (!$this->isCsrfTokenValid('remove_item' . $itemId, $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

        $order = $em->getRepository(Order::class)->find($orderId);
        $item = $em->getRepository(OrderItem::class)->find($itemId);

        if (!$order || !$item) {
            $this->addFlash('error', 'Commande ou article introuvable.');
            return $this->redirectToRoute('accueil_dashboard');
        }

        $order->removeOrderItem($item);
        $em->remove($item);

        $total = 0;
        foreach ($order->getItems() as $i) {
            $total += $i->getQuantity() * $i->getUnitPrice();
        }
        $order->setTotal(number_format($total, 2, '.', ''));

        $em->flush();
        $this->addFlash('success', 'Article supprimé de la commande.');

        return $this->redirectToRoute('accueil_order_show', ['id' => $order->getId()]);
    }

    #[Route('/orders/{id}/deliver', name: 'order_deliver', methods: ['POST'])]
    public function deliverOrder(int $id, Request $request, EntityManagerInterface $em): RedirectResponse
    {
        // Vérification du token CSRF
        if (!$this->isCsrfTokenValid('deliver' . $id, $request->request->get('_token'))) {
            // Message flash pour informer l'utilisateur
            $this->addFlash('error', 'Token CSRF invalide. Veuillez rafraîchir la page et réessayer.');
            return $this->redirectToRoute('accueil_order_show', ['id' => $id]);
        }

        // Récupération de la commande
        $order = $em->getRepository(Order::class)->find($id);
        if (!$order) {
            $this->addFlash('error', 'Commande introuvable.');
            return $this->redirectToRoute('accueil_dashboard');
        }

        // Tentative de marquer la commande comme livrée
        try {
            $order->marquerLivree(); // ou setStatut(Order::STATUT_LIVREE);
            $em->flush();
            $this->addFlash('success', 'Commande remise au client.');
        } catch (\LogicException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        // Redirection vers le dashboard ou la page de la commande
        return $this->redirectToRoute('accueil_dashboard');
    }
}
