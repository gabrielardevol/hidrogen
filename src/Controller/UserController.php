<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Uid\Uuid;

class UserController extends AbstractController
{

    private $entityManager;
    private $passwordHasher;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher)
    {
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
    }

    public function registerUser(Request $request, EntityManagerInterface $em): JsonResponse
    {

        $data = json_decode($request->getContent(), true);
        $user = new User();

        $user->setFullName($data['fullName']);
        $user->setEmail($data['email']);

        $hashedPassword = $this->passwordHasher->hashPassword($user, $data['password']);
        $user->setPasswordHash($hashedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return new JsonResponse(
            [
                'id' => $user->getId(),
                'name' => $user->getFullName(),
            ],
            201
        );
    }

    public function getUserById(string $id , UserRepository $userRepository): JsonResponse
    {
        $user = $userRepository->find($id);

        if (!$user) {
            return new JsonResponse(['error' => 'Usuari no trobat'], 404);
        }

        return new JsonResponse([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'bio' => $user->getBio(),
            'fullName' => $user->getFullName(),
            'mail' =>  $user->getEmail(),
            'avatarUrl' => $user->getAvatarUrl(),
        ]);
    }
}
