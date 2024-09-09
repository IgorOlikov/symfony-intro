<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegisterType;
use App\Message\SendEmailVerificationMessage;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\This;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class AuthController extends AbstractController
{

    public function __construct(
        private EntityManagerInterface $entityManager,
        private MessageBusInterface $messageBus,

    )
    {
    }

    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher

    ): Response
    {
        $user = new User();

        $form = $this->createForm(RegisterType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
           $user->setPassword($userPasswordHasher->hashPassword($user, $form->get('password')->getData()));

           $this->entityManager->persist($user);
           $this->entityManager->flush();




           // verification link email message
           $this->messageBus->dispatch(new SendEmailVerificationMessage());


           return $this->redirectToRoute('app_home');
        }



        return $this->render('auth/register.html.twig', [
            'form' => $form,
        ]);

    }

}
