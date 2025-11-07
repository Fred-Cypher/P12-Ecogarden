<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\WeatherService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class WeatherController extends AbstractController
{
    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    #[Route('/api/openweather', name: 'app_api_weather', methods: ['GET'])]
    public function getOpenWeatherHome(HttpClientInterface $httpClient): JsonResponse
    {
        $response = $httpClient->request(
            'GET',
            'https://openweathermap.org/api'
        );

        return new JsonResponse($response->getContent(), $response->getStatusCode(), [], true);
    }

    #[Route('/api/getweather/{city?}', name: 'app_api_getweather', methods: ['GET'])]
    public function getWeather(WeatherService $weatherService, ?string $city, #[CurrentUser] ?UserInterface $currentUser): JsonResponse
    {
        if ($city)
        {
            try{
                $weather = $weatherService->getWeatherForCity($city);
                return new JsonResponse($weather, Response::HTTP_OK);
            } catch (\Exception $e) {
                return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
            }
        } else {
            $city = $currentUser->getCity();
            $weather = $weatherService->getWeatherForCity($city);
            return new JsonResponse($weather, Response::HTTP_OK);
        }
    }
}
