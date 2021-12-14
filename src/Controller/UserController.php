<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/profile')]
#[IsGranted('ROLE_USER', message:'You need to be logged-in to access this resource')]
class UserController extends AbstractController
{
    #[Route('/{id<\d+>}', name:'app_user_profile', methods:['GET'])]
function profile(User $user): Response
    {
    return $this->render('user/posts.html.twig', [
        'user' => $user,
    ]);
}

#[Route('/{id<\d+>}/likes', name:'app_user_likes', methods:['GET'])]
function likes(User $user): Response
    {
    return $this->render('user/likes.html.twig', [
        'user' => $user,
    ]);
}

#[Route('/{id<\d+>}/comments', name:'app_user_comments', methods:['GET'])]
function comments(User $user): Response
    {
    return $this->render('user/comments.html.twig', [
        'user' => $user,
    ]);
}

#[Route('/{id<\d+>}/edit', name:'app_user_edit', methods:['GET', 'POST'])]
function edit(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
    $form = $this->createForm(UserType::class, $user);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->flush();
        $user->setMediaFile(null);

        return $this->redirectToRoute('app_user_profile', ['id' => $user->getId()], Response::HTTP_SEE_OTHER);
    }

    return $this->renderForm('user/edit.html.twig', [
        'user' => $user,
        'form' => $form,
    ]);
}

#[Route('/{id<\d+>}', name:'app_user_delete', methods:['POST'])]
function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
    if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
        $entityManager->remove($user);
        $entityManager->flush();
    }

    return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
}
}