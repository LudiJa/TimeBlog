<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Form\CommentType;
use App\Form\PostType;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class PostController extends AbstractController
{
    #[Route('/', name:'app_home')]
function index(PostRepository $postRepo): Response
    {
    // $arrayPosts = $postRepo->findAll();
    $arrayPosts = $postRepo->findBy([], ['createdAt' => 'DESC']);

    return $this->render('post/index.html.twig', ['posts' => $arrayPosts]);
}

#[Route('/post/{id<\d+>}', name:'app_post_details', methods:['GET|POST'])]
function details(Post $post, Request $request, EntityManagerInterface $em, Security $security): Response
    {
    $comment = new Comment();
    $form = $this->createForm(CommentType::class, $comment);
    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
        $user = $security->getUser();
        $comment->setUser($user);
        $post->addComment($comment);
        // ou $comment->setPost($post)
        $em->persist($comment);
        $em->flush();
    }

    $likes = $post->getLikes();
    $comments = $post->getComments();

    return $this->renderForm('post/details.html.twig', ['post' => $post, 'comments' => $comments, 'form' => $form, 'likes' => $likes]);
}

// creation de post
#[Route('/post/create', name:'app_post_create')]
#[IsGranted('ROLE_USER', message:'You need to be logged-in to access this resource.')]
function create(Request $request, EntityManagerInterface $em, Security $security): Response
    {
    $post = new Post();
    $formulaire = $this->createForm(PostType::class, $post);
    $formulaire->handleRequest($request);
    if ($formulaire->isSubmitted() && $formulaire->isValid()) {
        // recupère les infos de l'utilisateur et l'assigne en auteur du post
        $user = $security->getUser();
        $post->setUser($user);

        // persiste les infos (se prépare à les mettre en bdd)
        $em->persist($post);
        // les envoie dans la base de données
        $em->flush();

        return $this->redirectToRoute('app_home');
    }

    return $this->renderForm('post/create.html.twig', ['form' => $formulaire, 'action' => 'Create']);
}

// edition/suppression de post
#[Route('/post/edit/{id<\d+>}', name:'app_post_edit')]
#[IsGranted('ROLE_USER', message:'You need to be logged-in to access this resource.')]
function edit(Post $post, Request $request, EntityManagerInterface $em, Security $security): Response
    {
    $user = $security->getUser();
    if ($user === $post->getUser()) {

        $formulaire = $this->createForm(PostType::class, $post);
        $formulaire->handleRequest($request);
        if ($formulaire->isSubmitted() && $formulaire->isValid()) {
            $em->flush();

            return $this->redirectToRoute('app_home');
        }
        return $this->renderForm('post/create.html.twig', ['form' => $formulaire, 'action' => 'Edit']);
    }
    return $this->redirectToRoute('app_home');
}

#[Route('/post/delete/{id<\d+>}', name:'app_post_delete', methods:['POST'])]
#[IsGranted('ROLE_USER', message:'You need to be logged-in to access this resource.')]
// Post $post injection de dépendance, pareil et plus opti que findOneBy
function delete(Request $request, Post $post, EntityManagerInterface $em, Security $security): Response
    {
    $user = $security->getUser();
    if ($user === $post->getUser()) {
        if ($this->isCsrfTokenValid('delete' . $post->getId(), $request->request->get('_token'))) {
            $em->remove($post);
            $em->flush();
            return $this->redirectToRoute('app_home');
        }
        return $this->render('post/delete.html.twig');
    }
    return $this->redirectToRoute('app_home');
}

// like
#[Route('/like/{id}', name:'app_post_like')]
#[IsGranted('ROLE_USER', message:'You need to be logged-in to access this resource')]
function like(Post $post, Security $security, EntityManagerInterface $em): Response
    {
    $user = $security->getUser();
    //? Si mon user a déja liké le post, alors il fait parti de l'array qui contient les post.
    if ($post->getLikes()->contains($user)) {
        //? Il faut l'en enlever
        $post->removeLike($user);
        $em->flush();

        return $this->redirectToRoute('app_post_details', ['id' => $post->getId()]);
    }
    //? Sinon il faut le rajouter
    $post->addLike($user);
    $em->flush();

    return $this->redirectToRoute('app_post_details', ['id' => $post->getId()]);
}

#[Route('/like/{id}/home', name:'app_post_likehome')]
#[IsGranted('ROLE_USER', message:'You need to be logged-in to access this resource')]
function likeFromHome(Post $post, Security $security, EntityManagerInterface $em): Response
    {
    $user = $security->getUser();
    //? Si mon user a déja liké le post, alors il fait parti de l'array qui contient les post.
    if ($post->getLikes()->contains($user)) {
        //? Il faut l'en enlever
        $post->removeLike($user);
        $em->flush();

        return $this->redirectToRoute('app_home');
    }
    //? Sinon il faut le rajouter
    $post->addLike($user);
    $em->flush();

    return $this->redirectToRoute('app_home');
}
}