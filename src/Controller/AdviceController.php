<?php

namespace App\Controller;

use App\Entity\Advice;
use App\Repository\AdviceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
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

    /**
     * @throws ExceptionInterface
     */
    #[Route('/api/advice/{id}', name: 'app_api_advice', methods: ['GET'])]
    public function getAdviceById(Advice $advice, SerializerInterface $serializer): JsonResponse
    {
        $advice = $serializer->serialize($advice, 'json', ['groups' => ['advice:read']]);
        return new JsonResponse($advice, Response::HTTP_OK, [], true);
    }

    #[Route('api/advice/{month}', name: 'app_api_advice_month', methods: ['GET'])]
    public function getAdviceMonth(Advice $advice): JsonResponse
    {

    }

    /**
     * @throws ExceptionInterface
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Route('api/advice', name: 'app_api_create_advice', methods: ['POST'])]
    public function createAdvice(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator): JsonResponse
    {
        $advice = $serializer->deserialize($request->getContent(), Advice::class, 'json');
        $advice->setAuthor($this->getUser());
        $em->persist($advice);
        $em->flush();

        $jsonAdvice = $serializer->serialize($advice, 'json', ['groups' => ['advice:read']]);

        $location = $urlGenerator->generate('app_api_advice', ['id' => $advice->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonAdvice, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    /**
     * @throws ExceptionInterface
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Route('api/advice/{id}', name: 'app_api_update_advice', methods: ['PUT'])]
    public function updateAdvice(Request $request, EntityManagerInterface $em, SerializerInterface $serializer, Advice $currenAdvice): JsonResponse
    {
        $updatedAdvice = $serializer->deserialize($request->getContent(), Advice::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $currenAdvice]);
        $em->persist($updatedAdvice);
        $em->flush();

        return new JsonResponse($updatedAdvice, Response::HTTP_NO_CONTENT);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('api/advice/{id}', name: 'app_api_advice_delete', methods: ['DELETE'])]
    public function deleteAdvice(Advice $advice, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($advice);
        $em->flush();

        return new JsonResponse(['message' => 'Advice deleted'], Response::HTTP_NO_CONTENT);
    }
}
