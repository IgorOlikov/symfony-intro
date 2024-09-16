<?php

namespace App\Security\Voter;

use App\Entity\Comment;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

final class CommentVoter extends Voter
{
    public const EDIT = 'edit';

    public const CREATE = 'create';

    public const VIEW = 'view';

    public function __construct(
        private Security $security,
    )
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::EDIT, self::CREATE ,self::VIEW])
            && $subject instanceof \App\Entity\Comment;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }


        $comment = $subject;

        // ... (check conditions and return true to grant permission) ...
        return match($attribute) {
            self::VIEW => $this->canView($comment, $user),
            self::CREATE => $this->canCreate($comment, $user),
            self::EDIT => $this->canEdit($comment, $user),
            default => false
        };
    }

    public function canView(Comment $comment, UserInterface $user): bool
    {
        return true;
    }

    public function canCreate(Comment $comment, UserInterface $user): bool
    {
       return $this->security->isGranted('ROLE_VERIF_USER');
    }

    public function canEdit(Comment $comment, UserInterface $user): bool
    {
        return $comment->getCommentOwner() === $user;
    }

}
