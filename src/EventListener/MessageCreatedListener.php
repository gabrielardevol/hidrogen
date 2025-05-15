<?php
// src/EventListener/MessageCreatedListener.php

namespace App\EventListener;

use App\Entity\Message;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

class MessageCreatedListener
{
    private HubInterface $hub;

    public function __construct(HubInterface $hub)
    {
        $this->hub = $hub;
    }


    public function postPersist(Message $message, LifecycleEventArgs $args): void
    {

        dump("message listener");
        $update = new Update(
            topics: 'chat/global',
            data: json_encode([
                'id' => $message->getId(),
                'content' => $message->getContent(),
                'createdAt' => $message->getCreatedAt()->format('c'),
                'sender' => $message->getAuthor()?->getFullName(),
            ])
        );

        $this->hub->publish($update);
    }
}
