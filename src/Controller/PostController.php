<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PostController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(PostRepository $postRepo): Response
    {
        $arrayPosts = $postRepo->findAll();

        // dd($arrayPost);

        return $this->render('post/index.html.twig', ['posts' => $arrayPosts]);
    }

    #[Route('/post/{id<\d+>}', name: 'app_post_details', methods:['GET'])]
    public function details(Post $post): Response {
        dd($post);
        return $this->render('post/index.html.twig', []);
    }

    #[Route('/post/create', name: 'app_post_create')]
    #[IsGranted('ROLE_USER', message: 'You need to be logged-in to access this resource.')]
    public function create(Request $request, EntityManagerInterface $em, Security $security): Response
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
    #[IsGranted('ROLE_USER', message: 'You need to be logged-in to access this resource.')]
    public function edit(Post $post, Request $request, EntityManagerInterface $em, Security $security): Response
    {
        $user = $security->getUser();
        if($user === $post->getUser()){

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

    #[Route('/post/{id<\d+>}', name:'app_post_delete', methods:['POST'])]
    #[IsGranted('ROLE_USER', message: 'You need to be logged-in to access this resource.')]
    // Post $post injection de dépendance, pareil et plus opti que findOneBy
    public function delete(Request $request, Post $post, EntityManagerInterface $em, Security $security): Response
    {
        $user = $security->getUser();
        if($user === $post->getUser()){
            if ($this->isCsrfTokenValid('delete'.$post->getId(), $request->request->get('_token'))){
            $em->remove($post);
            $em->flush();
            return $this->redirectToRoute('app_home');
            }
            return $this->render('post/delete.html.twig');
        }
        return $this->redirectToRoute('app_home');
    }
}