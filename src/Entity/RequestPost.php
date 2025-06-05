<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\RequestPostRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RequestPostRepository::class)]
#[ApiResource]
class RequestPost
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $PublisherUserId = null;

    #[ORM\Column(length: 2000)]
    private ?string $message = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $CreatedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPublisherUserId(): ?string
    {
        return $this->PublisherUserId;
    }

    public function setPublisherUserId(string $PublisherUserId): static
    {
        $this->PublisherUserId = $PublisherUserId;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->CreatedAt;
    }

    public function setCreatedAt(\DateTimeImmutable $CreatedAt): static
    {
        $this->CreatedAt = $CreatedAt;

        return $this;
    }
}
