<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use App\Form\OrderStatutType;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin/order', name: 'app_admin_order_')]
class OrderController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(OrderRepository $orderRepo): Response
    {
        // Tous les rôles voient toutes les commandes
        $orders = $orderRepo->findAll();

        return $this->render('admin_order/index.html.twig', [
            'orders' => $orders,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Order $order): Response
    {
        // Tous les rôles peuvent voir une commande
        return $this->render('admin_order/show.html.twig', [
            'order' => $order,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Order $order, Request $request, EntityManagerInterface $em): Response
    {
        // Autorisation : Super admin ou préparateur
        if (!$this->isGranted('ROLE_SUPER_ADMIN') && !$this->isGranted('ROLE_PREPARATEUR')) {
            throw $this->createAccessDeniedException('Accès refusé.');
        }

        $form = $this->createForm(OrderStatutType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Statut de la commande mis à jour.');
            return $this->redirectToRoute('app_admin_order_index');
        }

        return $this->render('admin_order/edit.html.twig', [
            'order' => $order,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/cancel', name: 'cancel', methods: ['POST'])]
    public function cancel(Order $order, Request $request, EntityManagerInterface $em): Response
    {
        if (!$this->isGranted('ROLE_SUPER_ADMIN') && !$this->isGranted('ROLE_PREPARATEUR')) {
            throw $this->createAccessDeniedException('Accès refusé.');
        }

        if (!$this->isCsrfTokenValid('cancel' . $order->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

        $order->setStatut('Annulée');
        $em->flush();
        $this->addFlash('success', 'Commande annulée.');

        return $this->redirectToRoute('app_admin_order_index');
    }

    #[Route('/{id}/serve', name: 'serve', methods: ['POST'])]
    public function serve(Order $order, Request $request, EntityManagerInterface $em): Response
    {
        if (!$this->isGranted('ROLE_SUPER_ADMIN') && !$this->isGranted('ROLE_PREPARATEUR')) {
            throw $this->createAccessDeniedException('Accès refusé.');
        }

        if (!$this->isCsrfTokenValid('serve' . $order->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

        $order->setStatut('Servie');
        $em->flush();
        $this->addFlash('success', 'Commande servie.');

        return $this->redirectToRoute('app_admin_order_index');
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Order $order, Request $request, EntityManagerInterface $em): Response
    {
        // Autorisation : uniquement super admin
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');

        // Vérifier le token CSRF
        if (!$this->isCsrfTokenValid('delete' . $order->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

        $em->remove($order);
        $em->flush();

        $this->addFlash('success', 'Commande supprimée avec succès.');
        return $this->redirectToRoute('app_admin_order_index');
    }
}
