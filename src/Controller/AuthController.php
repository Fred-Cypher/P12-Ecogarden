<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final class AuthController extends AbstractController
{
    #[Route('/api/auth', name: 'api_auth')]
    public function login(#[CurrentUser] ?object $user): JsonResponse
    {
        if (null === $user) {
            return new JsonResponse([
                'message' => 'Identifiants invalides'
            ], 401);
        }

        return new JsonResponse([
            'status' => 'success',
            'message' => 'Authentification réussie',
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getUserIdentifier(),
                'roles' => $user->getRoles()
            ]
        ]);
    }
}
