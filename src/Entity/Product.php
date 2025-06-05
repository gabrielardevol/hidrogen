<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Controller\ProductController;
use App\Repository\ProductRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ApiResource(
    operations: [

        new Delete(
            uriTemplate: '/products/{id}',
            controller: ProductController::class . '::deleteProduct',
        ),
        new GetCollection(
            uriTemplate: '/productsByUser/{userId}', //relative to user, NOT MADE BY USER
            controller: ProductController::class . '::getAllCompact',
            read: false,
            deserialize: false,
            name: 'get_compact_products',
        ),
        new GetCollection(
          uriTemplate: '/productsPostedBy/{userId}',
          controller: ProductController::class . '::searchByUserId',
            read: false,
            deserialize: false,
            name: 'get_products_made_by_user',
        ),
        new Post(
            uriTemplate: '/products',
            controller: ProductController::class . '::createProduct',
            read: false,
            deserialize: true
        ),
        new GetCollection(
            uriTemplate: '/productsByTerm/{userId}',
            controller: ProductController::class . '::searchByTerm',
            read: false,
            deserialize: false,
            name: 'search_products',
        ),
        new Post(
            uriTemplate: '/products/{productId}/reserve/{userId}',
            controller: ProductController::class . '::reserveProduct',
        ),
        new Post(
            uriTemplate: '/products/{productId}/sell/{userId}',
            controller: ProductController::class . '::sellProduct',
        ),
        new Get(
            uriTemplate: '/products/{id}/{userId}',
            controller: ProductController::class . '::getById',
        ),
        new Post(
            uriTemplate: '/productAssignBuyer/{productId}/{buyerId}',
            controller: ProductController::class . '::assignBuyer',
        )

    ]
)] class Product
{



    #[ORM\Id]
    #[ORM\Column]
    private string $id;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $buyerId = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $createdAt = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $updatedAt = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $deletedAt = null;

    #[ORM\Column(type: 'array')]
    private array $images = [];

    #[ORM\Column(length: 4000)]
    private ?string $description = null;

    #[ORM\Column]
    private ?int $remuneration = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $thumbnailRatio = null;

    public function __construct()
    {
        $this->id = Uuid::v4();
        $this->images = [];
    }
    public function getId(): ?string
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }


    public function getBuyerId(): ?string
    {
        return $this->buyerId;
    }

    public function setBuyerId(?string $buyerId): static
    {
        $this->buyerId = $buyerId;

        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getDeletedAt(): ?\DateTime
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?\DateTime $deletedAt): static
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    public function getImages(): array
    {
        return $this->images;
    }

    public function setImages(array $images): static
    {
        $this->images = $images;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getRemuneration(): ?int
    {
        return $this->remuneration;
    }

    public function setRemuneration(int $remuneration): static
    {
        $this->remuneration = $remuneration;

        return $this;
    }

    public function getThumbnailRatio(): ?float
    {
        return $this->thumbnailRatio;
    }

    public function setThumbnailRatio(?float $thumbnailRatio): self
    {
        $this->thumbnailRatio = $thumbnailRatio;

        return $this;
    }
}
