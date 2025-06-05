<?php

namespace App\Controller;

use App\Entity\Chat;
use App\Entity\User;
use App\Repository\ChatRepository;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ChatController extends AbstractController
{
    private $em;
    private $repo;

    public function __construct(EntityManagerInterface $entityManager, ChatRepository $chatRepository)
    {
        $this->em = $entityManager;
        $this->repo = $chatRepository;

    }

    /**
     * @param Request $request
     * @param $userRepository
     * @return JsonResponse
     * @throws \Exception
     */

    public function getChats(
        string $userId,
        UserRepository $userRepository,
        ProductRepository $productRepository
    ): JsonResponse {
        $chats = $this->repo->findByUserId($userId);
        $response = [];

        foreach ($chats as $chat) {
            $publishingUser = $chat->getPublishingUser();
            $interestedUser = $chat->getInterestedUser();

            $chatData = [
                'id' => $chat->getId(),
                'publishingUserId' => $publishingUser->getId(),
                'publishingUserName' => $publishingUser->getFullName(),
                'publishingUserPfp' => $publishingUser->getAvatarUrl(),
                'interestedUserId' => $interestedUser->getId(),
                'interestedUserName' => $interestedUser->getFullName(),
                'interestedUserPfp' => $interestedUser->getAvatarUrl(),
                'subjectId' => $chat->getSubjectId(),
                'messages' => $chat->getMessages(),
            ];

            $product = $productRepository->find($chat->getSubjectId());
            if ($product) {
                $chatData['subjectType'] = 0;
                $chatData['productImg'] = $product->getImages()[0] ?? null;
                $chatData['productName'] = $product->getName();
            }

            $response[] = $chatData;
        }

        usort($response, function ($a, $b) {
            $aMessages = $a['messages'];
            $bMessages = $b['messages'];

            $aLastDate = !empty($aMessages) ? end($aMessages)['date'] : null;
            $bLastDate = !empty($bMessages) ? end($bMessages)['date'] : null;

            $aTimestamp = $aLastDate ? $aLastDate->getTimeStamp() : 0;
            $bTimestamp = $bLastDate ? $bLastDate->getTimeStamp() : 0;

            return $bTimestamp <=> $aTimestamp;
        });

        return $this->json($response);
    }

    public function getChat(string $chatId, ProductRepository $productRepository): JsonResponse
    {
        $chat = $this->repo->find($chatId);
        $publishingUser = $chat->getPublishingUser();
        $interestedUser = $chat->getInterestedUser();

        $response = [
            'id' => $chat->getId(),
            'publishingUserId' => $publishingUser->getId(),
            'publishingUserName' => $publishingUser->getFullName(),
            'publishingUserPfp' => $publishingUser->getAvatarUrl(),
            'interestedUserId' => $interestedUser->getId(),
            'interestedUserName' => $interestedUser->getFullName(),
            'interestedUserPfp' => $interestedUser->getAvatarUrl(),
            'messages' => $chat->getMessages(), // Potser has de serialitzar-los!
            'subjectId' => $chat->getSubjectId(),
            'state' => $chat->getState()
        ];
        $product = $productRepository->find($chat->getSubjectId());
        if ($product) {
            $response['subjectType'] = 0; // 0 = product
            $response['productImg'] = $product->getImages()[0];
            $response['productName'] = $product->getName();
        }

        return $this->json($response);
    }

    public function getChatByUsersAndSubject(        string $publishingUserId,        string $interestedUserId,        string $subjectId,        UserRepository $userRepository,        ProductRepository $productRepository    ): JsonResponse {
        $interestedUser = $userRepository->find($interestedUserId);
        $publishingUser = $userRepository->find($publishingUserId);

        if (!$interestedUser || !$publishingUser) {
            return new JsonResponse(['error' => 'Usuari/s no trobat/s.'], Response::HTTP_NOT_FOUND);
        }

        try {
            $chat = $this->repo->findByUsersAndSubject($interestedUser, $publishingUser, $subjectId);
        } catch (\RuntimeException $e) {
            return new JsonResponse(['error' => 'Chat not found.'], Response::HTTP_NOT_FOUND);
        }

        $response = [
            'id' => $chat->getId(),
            'publishingUserId' => $publishingUser->getId(),
            'publishingUserName' => $publishingUser->getFullName(),
            'publishingUserPfp' => $publishingUser->getAvatarUrl(),
            'interestedUserId' => $interestedUser->getId(),
            'interestedUserName' => $interestedUser->getFullName(),
            'interestedUserPfp' => $interestedUser->getAvatarUrl(),
            'messages' => $chat->getMessages(), // Potser has de serialitzar-los!
            'subjectId' => $chat->getSubjectId(),
        ];

        $product = $productRepository->find($chat->getSubjectId());
        if ($product) {
            $response['subjectType'] = 0; // 0 = product
            $response['productImg'] = $product->getImages()[0];
            $response['productName'] = $product->getName();
        }

        return $this->json($response);
    }

    public function createChat(Request $request, UserRepository $userRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        dump($request);
        $chat = new Chat();
        $chat->setInterestedUser($userRepository->find($data["interestedUserId"]));
        $chat->setPublishingUser($userRepository->find($data["publishingUserId"]));
        $chat->setSubjectId($data["subjectId"]);

        $this->em->persist($chat);
        $this->em->flush();
        return new JsonResponse(
            [
                'id' => $chat->getId(),
            ],
            201
        );
    }

    public function newMessage(Request $request, string $chatId): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        dump($data);
        $chat = $this->repo->find($chatId);
        $message = [
            'user' => $data['user'],
            'content' => $data['content'],
            'date' => new \DateTime(),
        ];
        $chat->setMessage($message);

        $this->em->flush();
        return new JsonResponse(
            $chat->getMessages(),
            201
        );
    }

    public function updateState(Request $request, string $chatId, int $state): JsonResponse
    {
        $chat = $this->repo->find($chatId);
        $chat->setState($state);
        $this->em->flush();
        return new JsonResponse(
            "product has been requested",
            201
        );
    }

}
