<?php

namespace App\MessageHandler;

use App\Entity\User;
use App\Message\SendEmailVerificationMessage;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class SendEmailVerificationMessageHandler
{
    public function __construct(
        //private MailerInterface $mailer,
        private LoggerInterface $logger)
    {
    }

    public function __invoke(SendEmailVerificationMessage $message): void
    {
        $user = $message->getUser();

        $signedUrl = $this->generateSignedUrl($user);


        /**
        $email = (new TemplatedEmail())
            ->from('simple_symfony_app')
            ->to($user->getEmail())
            ->subject('Verify your account!!!')
            ->htmlTemplate('mail/email-verification.html.twig')
            ->context([
                'userName' => $user->getName(),
                'signedUrl' => $signedUrl
            ]);

        $this->mailer->send($email);

        */
    }

    public function generateSignedUrl(User $user): string
    {
        $url = 'http://localhost/verify-email';

        $userId = $user->getUserIdentifier();

        $expires = (new \DateTimeImmutable())->add(new \DateInterval('PT2H'))->getTimestamp(); // example 1674057635586

        $hash = sha1($user->getEmail());

        $url = $url . '/' . $userId . '/' . $hash . '?' . 'expires=' . $expires;

        $signature = hash_hmac('sha256', $url, $_ENV['APP_SECRET']);

        $verificationUrl = $url . '&' . 'signature=' . $signature;

        //debug log
        $this->logger->log(3, $verificationUrl);

        return $verificationUrl;
    }
}
