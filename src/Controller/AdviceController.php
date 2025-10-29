<?php

namespace App\Controller;

use App\Entity\Advice;
use App\Enum\Month;
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
     * Retrieving all advices
     * @throws ExceptionInterface
     */
    #[Route('/api/advices', name: 'app_api_advices', methods: ['GET'])]
    public function getAllAdvices(AdviceRepository $adviceRepository, SerializerInterface $serializer): JsonResponse
    {
        $advices = $adviceRepository->findAll();

        if (!$advices) {
            return $this->jsonError('Aucun conseil trouvé', Response::HTTP_NOT_FOUND);
        }

        $advicesList = $serializer->serialize($advices, 'json', ['groups' => ['advice:read']]);

        return new JsonResponse($advicesList, Response::HTTP_OK, [], true);
    }

    /**
     * Retrieving advice by its id (used for testing)
     * @throws ExceptionInterface
     */
    #[Route('/api/adviceId/{id}', name: 'app_api_advice', methods: ['GET'])]
    public function getAdviceById(Advice $advice, SerializerInterface $serializer): JsonResponse
    {
        $advice = $serializer->serialize($advice, 'json', ['groups' => ['advice:read']]);
        return new JsonResponse($advice, Response::HTTP_OK, [], true);
    }

    /**
     * Retrieving advice by month
     * @throws ExceptionInterface
     */
    #[Route('/api/advice/{month}', name: 'app_api_advice_month', methods: ['GET'])]
    public function getAdvicesByMonth(
        AdviceRepository $adviceRepository,
        SerializerInterface $serializer,
        int $month
    ): JsonResponse {
        try {
            $monthEnum = Month::from($month);
        } catch (\ValueError) {
            return $this->jsonError('Mois invalide. Veuillez utiliser un mois existant.', Response::HTTP_BAD_REQUEST);
        }

        $advices = $adviceRepository->findAdvicesByMonth($monthEnum);

        $frenchMonth = $monthEnum->label();

        if (!$advices) {
            return $this->jsonError("Aucun conseil trouvé pour $frenchMonth", Response::HTTP_NOT_FOUND);
        }

        $monthAdvices = $serializer->serialize($advices, 'json', ['groups' => ['advice:read']]);

        return new JsonResponse($monthAdvices, Response::HTTP_OK, [], true);
    }

    /**
     * Retrieve advices for the current month
     */
    #[Route('/api/advice', name: 'app_api_advice_currentmonth', methods: ['GET'])]
    public function getAdvicesCurrentMonth(
        AdviceRepository $adviceRepository,
        SerializerInterface $serializer
    ): JsonResponse {
        $now = new \DateTimeImmutable('now');
        $currentMonthNumber = (int)$now->format('n');

        $currentMonth = Month::from($currentMonthNumber);

        $advices = $adviceRepository->findAdvicesByMonth($currentMonth);

        if (!$advices) {
            return $this->jsonError('Aucun conseil trouvé pour le mois en cours', Response::HTTP_NOT_FOUND);
        }
        $currentMonthAdvices = $serializer->serialize($advices, 'json', ['groups' => ['advice:read']]);

        return new JsonResponse($currentMonthAdvices, Response::HTTP_OK, [], true);
    }

    /**
     * Create a new advice
     * @throws ExceptionInterface
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/api/advice', name: 'app_api_create_advice', methods: ['POST'])]
    public function createAdvice(
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $em,
        UrlGeneratorInterface $urlGenerator
    ): JsonResponse {
        try {
            $advice = $serializer->deserialize($request->getContent(), Advice::class, 'json');
        } catch (\Exception $e) {
            return $this->jsonError('Données non valides : ' . $e->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $advice->setAuthor($this->getUser());
        $em->persist($advice);
        $em->flush();

        $jsonAdvice = $serializer->serialize($advice, 'json', ['groups' => ['advice:read']]);

        $location = $urlGenerator->generate(
            'app_api_advice',
            ['id' => $advice->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        return new JsonResponse($jsonAdvice, Response::HTTP_CREATED, ['Location' => $location], true);
    }

    /**
     * Edit an advice
     * @throws ExceptionInterface
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/api/advice/{id}', name: 'app_api_update_advice', methods: ['PUT'])]
    public function updateAdvice(
        Request $request,
        EntityManagerInterface $em,
        SerializerInterface $serializer,
        Advice $currentAdvice
    ): JsonResponse {
        if (!$currentAdvice) {
            return $this->jsonError('Conseil introuvable', Response::HTTP_NOT_FOUND);
        }

        try {
            $serializer->deserialize(
                $request->getContent(),
                Advice::class,
                'json',
                [AbstractNormalizer::OBJECT_TO_POPULATE => $currentAdvice]
            );
        } catch (\Exception $e) {
            return $this->jsonError('Données non valides : ' . $e->getMessage());
        }

        $em->flush();

        $updatedAdvice = $serializer->serialize($currentAdvice, 'json', ['groups' => ['advice:read']]);


        return new JsonResponse($updatedAdvice, Response::HTTP_OK, [], true);
    }

    /**
     * Delete an advice
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/api/advice/{id}', name: 'app_api_advice_delete', methods: ['DELETE'])]
    public function deleteAdvice(Advice $advice, EntityManagerInterface $em): JsonResponse
    {
        if (!$advice) {
            return $this->jsonError('Conseil introuvable', Response::HTTP_NOT_FOUND);
        }

        $em->remove($advice);
        $em->flush();

        return new JsonResponse(['message' => 'Conseil supprimé'], Response::HTTP_NO_CONTENT);
    }

    private function jsonError(string $message, int $status = Response::HTTP_BAD_REQUEST): JsonResponse
    {
        return new JsonResponse(['error' => $message], $status);
    }
}
