<?php

namespace App\EntityListener;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\String\Slugger\SluggerInterface;

#[AsEntityListener(event: Events::prePersist, entity: Post::class)]
#[AsEntityListener(event: Events::preUpdate, entity: Post::class)]
class PostEntityListener
{
    public function __construct(
        private SluggerInterface $slugger
    )
    {
    }

    public function prePersist(Post $post, LifecycleEventArgs $event): void
    {
        $post->computeSlug($this->slugger);
    }

    public function preUpdate(Post $post, LifecycleEventArgs $event): void
    {
        $post->computeSlug($this->slugger);
    }

}