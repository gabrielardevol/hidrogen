<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Controller\ChatController;
use App\Controller\MessageController;
use App\Repository\ChatRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;


#[ORM\Entity(repositoryClass: ChatRepository::class)]
#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/chatByUsersAndSubject/{publishingUserId}/{interestedUserId}/{subjectId}',
            controller: ChatController::class . '::getChatByUsersAndSubject',
            read: false,
            deserialize: true
        ),
        new Post(
            uriTemplate: 'updateState/{chatId}/{state}',
            controller: ChatController::class . '::updateState'
        ),
        new Get(
            uriTemplate: 'chat/{chatId}',
            controller: ChatController::class . '::getChat',
        ),
        new Post(
            uriTemplate: 'chats',
            controller: ChatController::class . '::createChat'
        ),
        new GetCollection(
            uriTemplate: 'chats/{userId}',
            controller: ChatController::class . '::getChats',
        ),
        new Post(
            uriTemplate: 'message/{chatId}',
            controller: ChatController::class . '::newMessage'
        ),

    ]
)]
class Chat
{
    #[ORM\Id]
    #[ORM\Column]
    private ?string $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $publishingUser = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $interestedUser = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $subjectId = null;

    #[ORM\Column(nullable: true)]
    private ?int $subjectType = null;

    #[ORM\Column(type: Types::ARRAY)]
    private array $messages = [];

    #[ORM\Column]
    private ?int $state = null;


    public function __construct()
    {
        $this->id = Uuid::v4();
        $this->messages = [];
        $this->subjectId = null;
        $this->state = 0;

    }
    public function getId(): ?string
    {
        return $this->id;
    }

    public function getPublishingUser(): ?User
    {
        return $this->publishingUser;
    }

    public function setPublishingUser(User $publishingUser): static
    {
        $this->publishingUser = $publishingUser;

        return $this;
    }

    public function getInterestedUser(): ?User
    {
        return $this->interestedUser;
    }

    public function setInterestedUser(User $interestedUser): static
    {
        $this->interestedUser = $interestedUser;

        return $this;
    }

    public function getSubjectId(): ?string
    {
        return $this->subjectId;
    }

    public function setSubjectId(?string $subjectId): static
    {
        $this->subjectId = $subjectId;

        return $this;
    }

    public function getSubjectType(): ?int
    {
        return $this->subjectType;
    }

    public function setSubjectType(?int $subjectType): static
    {
        $this->subjectType = $subjectType;

        return $this;
    }

    public function getMessages(): array
    {
        return $this->messages;
    }

    public function setMessages(array $messages): static
    {
        $this->messages = $messages;

        return $this;
    }

    public function setMessage(array $message) : static
    {
        $messages = $this->messages;
        array_push($messages, $message);
        $this->messages = $messages;
        dump($this->messages);
        return $this;
    }

    public function getState(): ?int
    {
        return $this->state;
    }

    public function setState(int $state): static
    {
        $this->state = $state;

        return $this;
    }
}
