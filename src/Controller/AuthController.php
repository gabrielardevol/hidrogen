<?php
// src/Controller/AuthController.php

namespace App\Controller;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class AuthController extends AbstractController
{
    #[Route('/login', name: 'login', methods: ['POST'])]
    public function login(Security $security, JWTManager $jwtManager): JsonResponse
    {
//        $user = $this->getUser();
//
//        $token = $jwtManager->create($user);
//
//        return new JsonResponse(['token' => $token]);
    }
}
