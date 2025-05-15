<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\Product;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Mercure\Update;
use Psr\Log\LoggerInterface;

class MessageController extends AbstractController
{
    private $hub;
    private $em;
    private $logger;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, HubInterface $hub, LoggerInterface $logger)
    {
        $this->em = $entityManager;
        $this->hub = $hub;
        $this->logger = $logger;

    }

    /**
     * @param Request $request
     * @param $userRepository
     * @return JsonResponse
     * @throws \Exception
     */
    public function createMessage(Request $request, UserRepository $userRepository): JsonResponse
    {

        $data = json_decode($request->getContent(), true);

        dump($data);

        $this->logger->info('Data rebuda', $data ?: []);

        $message = new Message();
        $message->setAuthor( $userRepository->find($data['author']));
        $message->setDestinatary($userRepository->find($data['destinatary']));
        $message->setContent($data['content']);
        $message->setCreatedAt(new \DateTime());
        $message->setProduct($data['product']);
        $this->em->persist($message);
        $this->em->flush();

        $mercureData =   json_encode([
            'id' => $message->getId(),
            'author' => $message->getAuthor(),
            'message' => $message->getContent(),
            'createdAt' => $message->getCreatedAt()->format('c'),
        ]);

        $update = new Update(
            'https://chat/messages',          // topic (ha de ser un identificador únic i consistent)
            $mercureData
        );

        $this->logger->info('Publicant update Mercure');
        $this->hub->publish($update);
        $this->logger->info('Publicat update Mercure');

        return new JsonResponse(['status' => 'message sent']);

    }

}
