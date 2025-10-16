<?php

namespace App\Controller;

use App\Entity\Advice;
use App\Repository\AdviceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\SerializerInterface;

final class AdviceController extends AbstractController
{
    /**
     * @throws ExceptionInterface
     */
    #[Route('/api/advices', name: 'app_api_advices', methods: ['GET'])]
    public function getAllAdvices(AdviceRepository $adviceRepository, SerializerInterface $serializer): JsonResponse
    {
        $advices = $adviceRepository->findAll();

        $advicesList = $serializer->serialize($advices, 'json', ['groups' => ['advice:read']]);
        return new JsonResponse($advicesList, Response::HTTP_OK, [], true);
    }

    #[Route('api/advice/{month}', name: 'app_api_advice_month', methods: ['GET'])]
    public function getAdviceMonth(Advice $advice): JsonResponse
    {

    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('api/advice', name: 'app_api_create_advice', methods: ['POST'])]
    public function createAdvice(Advice $advice): JsonResponse
    {

    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('api/advice/{id}', name: 'app_api_update_advice', methods: ['PUT'])]
    public function updateAdvice(Advice $advice): JsonResponse
    {

    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('api/advice/{id}', name: 'app_api_advice_delete', methods: ['DELETE'])]
    public function deleteAdvice(Advice $advice): JsonResponse
    {

    }
}
