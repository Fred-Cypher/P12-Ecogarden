<?php

namespace App\Controller;

use App\Repository\AdviceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
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
}
