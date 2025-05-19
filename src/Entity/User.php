<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\ProductController;
use App\Controller\UserController;
use App\Repository\UserRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;
use function Symfony\Component\Clock\now;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'app_user')]
#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/users/{id}',
            controller: UserController::class . '::getUserById',
            read: false,
            deserialize: true
        ),
        new Get(
            uriTemplate: '/userPfp/{id}',
            controller: UserController::class . '::getPfp',
            read: false,
            deserialize: true
        ),
        new GetCollection(),
        new Post(
            uriTemplate: '/users',
            controller: UserController::class . '::registerUser',
            read: false,
            deserialize: true
        ),
        new Post(
            uriTemplate: '/users/{userId}/favourites/{productId}',
            controller: UserController::class . '::addFavouriteProduct',
            read: false,
            deserialize: false,
            name: 'user_add_favourite_product'
        ),
        new Delete(
            uriTemplate: '/users/{userId}/favourites/{productId}',
            controller: UserController::class . '::removeFavouriteProduct',
            read: false,
            deserialize: false,
            name: 'user_remove_favourite_product'
        ),
        new Post(
            uriTemplate: '/updateUser',
            controller: UserController::class . '::updateUser'
        )
    ])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\Column]
    private string $id;

    #[ORM\Column(length: 123)]
    private string $fullName;

    #[ORM\Column(length: 123)]
    private string $email;

    #[ORM\Column(length: 123)]
    private ?string $passwordHash = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $createdAt = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $updatedAt = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $deletedAt = null;

    #[ORM\Column]
    private bool $isActive = false;

    #[ORM\Column]
    private bool $isVerified = false;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $lastLogin = null;

    #[ORM\Column(type: 'integer')]
    private int $role = 0;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $avatarUrl = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $bio = null;

    #[ORM\Column(type: 'array')]
    private array $favouriteProducts = [];

    public function __construct()
    {
        $this->id = Uuid::v4();
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
        $this->lastLogin = new DateTime();
        $this->isActive = false;
        $this->bio = null;
        $this->avatarUrl = "avatar.png";
        $this->role = 0;
        $this->favouriteProducts = [];


    }
    public function getId(): ?string
    {
        return $this->id;
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): static
    {
        $this->fullName = $fullName;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPasswordHash(): ?string
    {
        return $this->passwordHash;
    }

    public function setPasswordHash(string $passwordHash): static
    {
        $this->passwordHash = $passwordHash;

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

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function isVerified(): ?bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function getLastLogin(): ?\DateTime
    {
        return $this->lastLogin;
    }

    public function setLastLogin(\DateTime $lastLogin): static
    {
        $this->lastLogin = $lastLogin;

        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): static
    {
        $this->role = $role;

        return $this;
    }

    public function getAvatarUrl(): ?string
    {
        return $this->avatarUrl;
    }

    public function setAvatarUrl(?string $avatarUrl): static
    {
        $this->avatarUrl = $avatarUrl;

        return $this;
    }

    public function getBio(): ?string
    {
        return $this->bio;
    }

    public function setBio(?string $bio): static
    {
        $this->bio = $bio;

        return $this;
    }
    public function getPassword(): ?string
    {
        return $this->passwordHash;
    }

    public function getFavouriteProducts(): array
    {
        return $this->favouriteProducts;
    }

    public function addFavouriteProduct(string $productId): self
    {
        if (!in_array($productId, $this->favouriteProducts, true)) {
            $this->favouriteProducts[] = $productId;
        }

        return $this;
    }

    public function removeFavouriteProduct(string $productId): self
    {
        $this->favouriteProducts = array_filter(
            $this->favouriteProducts,
            fn($id) => $id !== $productId
        );

        $this->favouriteProducts = array_values($this->favouriteProducts);

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function getRoles(): array
    {
        $roles = [$this->role];

        // Garanteix almenys un rol
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function eraseCredentials(): void
    {
    }
}
