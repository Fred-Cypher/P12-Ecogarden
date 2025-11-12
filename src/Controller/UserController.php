<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

final class UserController extends AbstractController
{
    /**
     * Create a new user
     * @throws ExceptionInterface
     */
    #[Route('/api/user', name: 'app_api_create_user', methods: ['POST'])]
    public function createUser(
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse {
        $data = $request->getContent();

        try {
            $user = $serializer->deserialize($data, User::class, 'json', ['groups' => ['user:write']]);
        } catch (ExceptionInterface $e) {
            return $this->jsonError('Données invalides : ' . $e->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        $existingUser = $em->getRepository(User::class)->findOneBy(['email' => $user->getEmail()]);
        if ($existingUser) {
            return $this->jsonError('Cet utilisateur existe déjà', Response::HTTP_CONFLICT);
        }

        $hashedPassword = $passwordHasher->hashPassword($user, $user->getPassword());
        $user->setPassword($hashedPassword);

        $user->setCreatedAt(new \DateTimeImmutable());
        $user->setUpdatedAt(new \DateTimeImmutable());
        $user->setRoles(['ROLE_USER']);

        $em->persist($user);
        $em->flush();

        $jsonUser = $serializer->serialize($user, 'json', ['groups' => ['user:read']]);

        return new JsonResponse($jsonUser, Response::HTTP_CREATED, [], true);
    }

    /**
     * Edit a user
     * @throws ExceptionInterface
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/api/user/{id}', name: 'app_api_update_user', methods: ['PUT'])]
    public function updateUser(
        Request $request,
        User $user,
        EntityManagerInterface $em,
        SerializerInterface $serializer,
        UserPasswordHasherInterface $passwordHasher,
        UrlGeneratorInterface $urlGenerator
    ): JsonResponse {
        $data = $request->getContent();
        $updatedUser = $serializer->deserialize(
            $data,
            User::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $user]
        );

        if ($updatedUser->getPassword() !== null && $updatedUser->getPassword() !== '') {
            $hashedPassword = $passwordHasher->hashPassword($updatedUser, $updatedUser->getPassword());
            $updatedUser->setPassword($hashedPassword);
        }

        $updatedUser->setUpdatedAt(new \DateTimeImmutable());

        $em->flush();

        $jsonUser = $serializer->serialize($updatedUser, 'json', ['groups' => ['user:read']]);

        $location = $urlGenerator->generate(
            'app_api_get_user',
            ['id' => $user->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        return new JsonResponse($jsonUser, Response::HTTP_OK, ['Location' => $location], true);
    }

    /**
     * Delete a user
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/api/user/{id}', name: 'app_api_delete_user', methods: ['DELETE'])]
    public function deleteUser(User $user, EntityManagerInterface $em): JsonResponse
    {
        if (!$user) {
            return $this->jsonError('Utilisateur introuvable', Response::HTTP_NOT_FOUND);
        }

        $em->remove($user);
        $em->flush();

        return new JsonResponse(['message' => 'L\'utilisateur a bien été supprimé'], Response::HTTP_OK);
    }

    /**
     * Retrieving user by its id (used for testing)
     * @throws ExceptionInterface
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/api/user/{id}', name: 'app_api_get_user', methods: ['GET'])]
    public function getUserById(User $user, SerializerInterface $serializer): JsonResponse
    {
        $jsonUser = $serializer->serialize($user, 'json', ['groups' => ['user:read']]);
        return new JsonResponse($jsonUser, Response::HTTP_OK, [], true);
    }


    private function jsonError(string $message, int $status = Response::HTTP_BAD_REQUEST): JsonResponse
    {
        return new JsonResponse(['error' => $message], $status);
    }
}
