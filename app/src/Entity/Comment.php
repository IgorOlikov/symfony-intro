<?php

namespace App\Entity;

use App\Repository\CommentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: CommentRepository::class)]
class Comment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT, length: 255, nullable: false)]
    #[Assert\NotBlank(message: 'Поле не может быть пустым')]
    #[Assert\Length(min: 4, max: 255)]
    private string $content;


    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: false)]
    private \DateTimeImmutable $createdAt;


    //parent_id !!!!!!!!!!!!!!



    #[ORM\ManyToOne(targetEntity: 'user', inversedBy: 'comment')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false)]
    private User $user;


    #[ORM\ManyToOne(targetEntity: 'post', inversedBy: 'comment')]
    #[ORM\JoinColumn(name: 'post_id', referencedColumnName: 'id', nullable: false)]
    private Post $post;


    public function __construct(User $user, Post $post)
    {
        $this->user = $user;
        $this->post = $post;
        $this->createdAt = new \DateTimeImmutable();

    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;
        return $this;
    }

    /**
     * @return User
     */
    public function getCommentOwner(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     * @return Comment
     */
    public function setCommentOwner(User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function setCommentPost(Post $post): static
    {
        $this->post = $post;
        return $this;
    }

    public function getCommentPost(): Post
    {
        return $this->post;
    }


}
