<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegisterType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\FormLoginAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use App\Security\EmailVerifier;

class AuthController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private EmailVerifier $emailVerifier,
        private FormLoginAuthenticator $formLoginAuthenticator,
    )
    {
    }

    /**
     * @throws TransportExceptionInterface
     */
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        UserAuthenticatorInterface $userAuthenticator

    ): Response {
        $user = new User();

        $form = $this->createForm(RegisterType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($userPasswordHasher->hashPassword($user, $form->get('password')->getData()));

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            //log in
            $userAuthenticator
                ->authenticateUser(
                    $user,
                    $this->formLoginAuthenticator,
                    $request,
                    [(new RememberMeBadge())->enable()]
                );

            // send email
            $this->emailVerifier->sendEmailConfirmation(
                'app_verify_email',
                $user,
                (new TemplatedEmail())
                    ->from(new Address('symfony-application@example.com'))
                    ->to($user->getEmail())
                    ->subject('Please Confirm your email')
                    ->htmlTemplate('mail/email-verification.html.twig')
            );

            return $this->redirectToRoute('app_home');
        }

        return $this->render('auth/register.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyEmail(
        Request $request,
        TranslatorInterface $translator,
        UserRepository $userRepository,
        UserAuthenticatorInterface $userAuthenticator
    ): Response {
        $userId = $request->query->get('id');

        if (null === $userId) {
            return $this->redirectToRoute('app_home');
        }

        $user = $userRepository->find($userId);

        if (null === $user) {
            return $this->redirectToRoute('app_home');
        }

        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute('app_register');
        }

        // if not have auth session -> log in user
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            $userAuthenticator
                ->authenticateUser(
                    $user,
                    $this->formLoginAuthenticator,
                    $request,
                    [(new RememberMeBadge())->enable()]
                );
        }

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        $this->addFlash('success', 'Your email address has been verified.');

        return $this->redirectToRoute('app_home');
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
