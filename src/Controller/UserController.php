<?php
namespace App\Controller;

use App\Entity\Product;
use App\Entity\User;
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
       dump($request);
        $data = json_decode($request->getContent(), true);
        dump($data);
//        if (empty($data['fullName']) || empty($data['email']) || empty($data['password'])) {
//            return  new JsonResponse(
//                [
//                    "" => "missing data"
//                ],
//                422
//            );
//        }

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
        );    }
}
