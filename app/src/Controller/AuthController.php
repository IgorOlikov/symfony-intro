<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegisterType;
use App\Message\SendEmailVerificationMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;


class AuthController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private MessageBusInterface $messageBus
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
            $this->messageBus->dispatch(new SendEmailVerificationMessage($user));

            //test
            //$this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

            return $this->redirectToRoute('app_home');
        }

        return $this->render('auth/register.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/verify-email/{userId}/{hash}', name: 'app_verify_email')]
    public function verifyEmail(Request $request, int $userId, string $hash): Response
    {
        $urlArr = preg_split('{&signature=}', $request->getUri());

        $reqUrl = $urlArr[0];
        $reqSignature = $urlArr[1];

        $sign = hash_hmac('sha256', $reqUrl, $_ENV['APP_SECRET']);

        $reqTimestamp = $request->get('expires');

        $nowTimestamp = (new \DateTimeImmutable())->getTimestamp();

        if ($reqSignature !== $sign) {
            return (new Response())->setStatusCode(402, 'invalid signature');
        }

        if ($reqTimestamp > $nowTimestamp) {
            $user = $this->entityManager->getRepository(User::class)->findOneBy(['id' => $userId]);
            $user->setEmailVerifiedAt(new \DateTimeImmutable());

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_home');
        } else {
            return (new Response())->setStatusCode(402, 'Verification link expired');
        }

        return (new Response())->setStatusCode('402','Invalid verification uri');
    }

    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('auth/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route('/login/redirect', name: 'app_login_redirect')]
    public function redirectLogin(): Response
    {
        return $this->redirectToRoute('app_home');
    }
}
