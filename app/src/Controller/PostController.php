<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PostController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    )
    {
    }

    #[Route('/post', name: 'app_post')]
    public function index(): Response
    {
        //@TODO add pagination

        $posts = $this->entityManager->getRepository(Post::class)->findAll();



        return $this->render('post/index.html.twig', [
            'controller_name' => 'PostController',
            'posts' => $posts
        ]);
    }

    #[Route('/post/create', name: 'app_post_create')]
    public function create(Request $request)
    {
        $post = new Post();

        $post->setPostOwner($this->getUser());

        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($post);

            $this->entityManager->flush();

            return $this->redirectToRoute('app_post_show', ['slug' => $post->getSlug()]);
        }
        return $this->render('post/create.html.twig', ['form' => $form]);
    }

    #[Route('/post/{slug}', name: 'app_post_show')]
    public function show(
       #[MapEntity(mapping: ['slug' => 'slug'])] Post $post)
    {
        return $this->render('post/show.html.twig', ['post' => $post, 'user' => $post->getPostOwner()]);
    }
}
