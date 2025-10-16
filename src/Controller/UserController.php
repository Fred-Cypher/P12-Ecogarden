<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class UserController extends AbstractController
{
    #[Route('/api/user', name: 'app_api_create_user', methods: ['POST'])]
    public function createUser(User $user): JsonResponse
    {

    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/api/user/{id}', name: 'app_api_update_user', methods: ['PUT'])]
    public function updateUser(User $user): JsonResponse
    {

    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/api/user/{id}', name: 'app_api_delete_user', methods: ['DELETE'])]
    public function deleteUser(User $user): JsonResponse
    {

    }

}
