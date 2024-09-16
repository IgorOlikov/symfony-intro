<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Form\CommentType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CommentController extends AbstractController
{
    public function __construct(
       private EntityManagerInterface $entityManager
    )
    {
    }

    #[Route('/comment', name: 'app_comment')]
    public function index(): Response
    {
        return $this->render('comment/index.html.twig', [
            'controller_name' => 'CommentController',
        ]);
    }

    //#[IsGranted('create', 'comment' , 'Action not allowed', 403)]
    #[IsGranted('ROLE_VERIF_USER')]
    #[Route('/post/{slug}/comment/create/{comment}', name: 'app_create_comment',
        defaults: ['comment' => null]
    )]
    public function create(
        Request $request,
        #[MapEntity(mapping: ['comment' => 'id'])] ?Comment $parentComment,
        #[MapEntity(mapping: ['slug' => 'slug'])] Post $post
    )
    : RedirectResponse|Response
    {
        $user = $this->getUser();

        $comment = new Comment();

        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $comment->setCommentPost($post);
            $comment->setCommentOwner($user);
            $comment->setParentComment($parentComment);

            $this->entityManager->persist($comment);

            $this->entityManager->flush();

            return $this->redirectToRoute('app_post_show', ['slug' => $post->getSlug()]);
        }

        return $this->render('comment/create.html.twig', ['form' => $form]);
    }
}
