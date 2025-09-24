<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/user')]
#[IsGranted('ROLE_SUPER_ADMIN')]
final class AdminUserController extends AbstractController
{
    #[Route('/', name: 'app_admin_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('admin_user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_admin_user_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Hash du mot de passe
            $user->setPassword(
                $passwordHasher->hashPassword($user, $user->getPassword())
            );
            // $em est une variable conventionnelle pour représenter l’EntityManager de Doctrine.
            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Utilisateur créé avec succès !');

            return $this->redirectToRoute('app_admin_user_index');
        }

        return $this->render('admin_user/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_admin_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('admin_user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_user_edit', methods: ['GET', 'POST'])]
    public function edit(
        User $user,
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Hash le mot de passe uniquement s’il a été modifié
            if ($user->getPassword()) {
                $user->setPassword(
                    $passwordHasher->hashPassword($user, $user->getPassword())
                );
            }

            $em->flush();

            $this->addFlash('success', 'Utilisateur modifié avec succès !');

            return $this->redirectToRoute('app_admin_user_index');
        }

        return $this->render('admin_user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'app_admin_user_delete', methods: ['POST'])]
    public function delete(
        User $user,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        // /** @var User $user */ commentaire pour ide qui réaffirme le type et évite le soulignement en rouge dans l'autocomplétion n'est plus utile ici//

        // Empêcher la suppression de soi-même ou administrateur principal du site 
        $currentUser = $this->getUser();
        if ($currentUser instanceof User && $user->getId() === $currentUser->getId()) {
            $this->addFlash('error', 'Vous ne pouvez pas supprimer votre propre compte administrateur.');
            return $this->redirectToRoute('app_admin_user_index');
        }
        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
            $em->remove($user);
            $em->flush();
            $this->addFlash('success', 'Utilisateur supprimé !');
        }

        return $this->redirectToRoute('app_admin_user_index');
    }
}
