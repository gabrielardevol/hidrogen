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
            'avatarUrl' => $user->getAvatarUrl(),
        ]);
    }

    public function addFavouriteProduct(string $userId, string $productId)
    {
        $user = $this->entityManager->getRepository(User::class)->find($userId);
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], 404);
        }

        $user->addFavouriteProduct($productId);
        $this->entityManager->flush();

        return new JsonResponse(['status' => 'Product added to favourites']);
    }

    public function removeFavouriteProduct(string $userId, string $productId)
    {
        $user = $this->entityManager->getRepository(User::class)->find($userId);
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], 404);
        }

        $user->removeFavouriteProduct($productId);
        $this->entityManager->flush();

        return new JsonResponse(['status' => 'Product removed from favourites']);
    }

    public function updateUser(Request $request, UserRepository $userRepository): JsonResponse {

        $data = json_decode($request->request->get('data'), true); // Use $request->request per obtenir dades de formularis
        dump($data);
        $imageFiles = $request->files->get('images'); // Obtenir els fitxers d'imatges
        dump($imageFiles);

        $user = $userRepository->find($data['id']);
        dump($user);
        $user->setFullName($data['fullName']);
        $user->setBio($data['bio']);
        $user->setEmail($data['email']);


        $uploadsDir = $this->getParameter('uploads_directory');

        if ($imageFiles){
            $movedFile = $imageFiles[0]->move($uploadsDir, $imageFiles[0]->getClientOriginalName());

            $filename = $movedFile->getFilename();

            $user->setAvatarUrl($filename);
        }



        $this->entityManager->flush();

        return new JsonResponse([
            'message' => 'User updated successfully',
        ], 200);
    }

    public function getPfp(string $id, UserRepository $userRepository) {
        $user = $userRepository->find($id);
        return new JsonResponse(
            [
                'avatar' => $user->getAvatarUrl(),
            ]
        );

    }
}
