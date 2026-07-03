<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/users')]
#[IsGranted('ROLE_SUPER_ADMIN')]
final class UserController extends AbstractController
{
    #[Route('', name: 'admin_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('admin/user/index.html.twig', [
            'users' => $userRepository->findBy([], ['name' => 'ASC']),
        ]);
    }

    #[Route('/new', name: 'admin_user_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher,
    ): Response {
        $user = new User();
        $form = $this->createForm(UserType::class, $user, ['is_new' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plain = $form->get('plainPassword')->getData();
            $user->setPassword($hasher->hashPassword($user, $plain));
            $em->persist($user);
            $em->flush();
            $this->addFlash('success', 'Usuário criado com sucesso.');
            return $this->redirectToRoute('admin_user_index');
        }

        return $this->render('admin/user/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_user_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        User $user,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher,
    ): Response {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plain = $form->get('plainPassword')->getData();
            if ($plain) {
                $user->setPassword($hasher->hashPassword($user, $plain));
            }
            $em->flush();
            $this->addFlash('success', 'Usuário atualizado.');
            return $this->redirectToRoute('admin_user_index');
        }

        return $this->render('admin/user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete_user_' . $user->getId(), $request->request->get('_token'))) {
            $em->remove($user);
            $em->flush();
            $this->addFlash('success', 'Usuário excluído.');
        }
        return $this->redirectToRoute('admin_user_index');
    }
}
