<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Controller\MessageController;
use App\Controller\ProductController;
use App\Controller\UserController;
use App\Repository\MessageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MessageRepository::class)]
#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/messages',
            controller: MessageController::class . '::createMessage',
            read: false,
            deserialize: true
        ),
        new Get(
            uriTemplate: '/messages/{userId}/{chatId}',
            controller: MessageController::class . '::getChat',
            read: false,
            deserialize: true
        ),
    ]
)] class Message
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $author = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $destinatary = null;

    #[ORM\Column(length: 1024)]
    private ?string $content = null;

    #[ORM\Column(length: 255)]
    private ?string $product = null;

//    #[ORM\Column(length: 255)]
//    private ?string $request = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $createdAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;
        return $this;
    }

    public function getDestinatary(): ?User
    {
        return $this->destinatary;
    }

    public function setDestinatary( ?User $destinatary): static
    {
        $this->destinatary = $destinatary;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getProduct(): ?string
    {
        return $this->product;
    }

    public function setProduct(string $product): static
    {
        $this->product = $product;

        return $this;
    }

//    public function getRequest(): ?string
//    {
//        return $this->request;
//    }
//
//    public function setRequest(string $request): static
//    {
//        $this->request = $request;
//
//        return $this;
//    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
