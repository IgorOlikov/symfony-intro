<?php

namespace App\Message;

use App\Entity\User;

final class SendEmailVerificationMessage
{
    private User $user;

    public function __construct(
        User $user,
    ) {
        $this->user = $user;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getUserId(): ?int
    {
        return $this->user->getId();
    }

    public function getUserEmail(): string
    {
        return $this->user->getEmail();
    }
}
