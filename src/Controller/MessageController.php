<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\Product;
use App\Entity\User;
use App\Repository\ProductRepository;
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
    private $em;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;

    }

    /**
     * @param Request $request
     * @param $userRepository
     * @return JsonResponse
     * @throws \Exception
     */
    public function createMessage(Request $request, UserRepository $userRepository, HubInterface $hub): JsonResponse
    {
        dump('entered createMessage()');

        $data = json_decode($request->getContent(), true);

        dump($data);


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

        dump($update);
        dump($hub);
        dump($hub->publish($update));

        return new JsonResponse(['status' => 'message sent']);

    }

    public function getChat(string $chatId, string $userId, ProductRepository $productRepository) {
//        buscar a productRepository; tot lo que aparegui posar-
        $messages = $productRepository->find($chatId);


        foreach ($messages as $message) {
            dump($message->getBuyerId());

            $compactData[] = [
                'id' => $message->getId(),
            ];
        }


        dump($compactData);
        // ha de tornar publisherUserId i interestedUserId;
        return new JsonResponse($compactData);
    }

}
