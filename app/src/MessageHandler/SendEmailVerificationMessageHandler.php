<?php

namespace App\MessageHandler;

use App\Entity\User;
use App\Message\SendEmailVerificationMessage;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use function Symfony\Component\DependencyInjection\Loader\Configurator\env;

#[AsMessageHandler]
final class SendEmailVerificationMessageHandler
{
    public function __construct(private MailerInterface $mailer)
    {
    }

    public function __invoke(SendEmailVerificationMessage $message): void
    {
        $user = $message->getUser();

        $signedUrl = $this->generateSignedUrl($user);

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
    }

    public function generateSignedUrl(User $user): string
    {
        $url = 'http://localhost/verify-emal';

        $userId = $user->getUserIdentifier();

        $expires = (new \DateTimeImmutable())->add(new \DateInterval('PT2H'))->getTimestamp(); // example 1674057635586

        $hash = sha1($user->getEmail());

        $url = $url . '/' . $userId . '/' . $hash . '?' . 'expires=' . $expires;

        $signature = hash_hmac('sha256', $url, env('APP_SECRET'));

        return $url . '&' . 'signature' . $signature;
    }
}
