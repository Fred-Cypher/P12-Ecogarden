<?php

namespace App\Controller;

use App\Service\WeatherService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class WeatherController extends AbstractController
{
    /**
     * @param WeatherService $weatherService
     * @param string|null $city
     * @param UserInterface|null $currentUser
     * @return JsonResponse
     */
    #[Route('/api/getweather/{city?}', name: 'app_api_getweather', methods: ['GET'])]
    public function getWeather(WeatherService $weatherService, ?string $city, #[CurrentUser] ?UserInterface $currentUser): JsonResponse
    {
        if ($city)
        {
            try{
                $weather = $weatherService->getWeatherForCity($city);

                $description = $weather['weather'][0]['description'];
                $temperature = $weather['main']['temp'];
                $humidity = $weather['main']['humidity'];

                $meteo = ['Qualité du ciel' => $description, 'Température' => $temperature, 'Taux d\'humidité' => $humidity];

                return new JsonResponse($meteo, Response::HTTP_OK);
            } catch (\Exception $e) {
                return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
            }
        } else {
            $city = $currentUser->getCity();
            $weather = $weatherService->getWeatherForCity($city);

            $description = $weather['weather'][0]['description'];
            $temperature = $weather['main']['temp'];
            $humidity = $weather['main']['humidity'];

            $meteo = ['Qualité du ciel' => $description, 'Température' => $temperature, 'Taux d\'humidité' => $humidity];

            return new JsonResponse($meteo, Response::HTTP_OK);
        }
    }

    /**
     * Only used for tests to see all information
     * @param WeatherService $weatherService
     * @param string|null $city
     * @param UserInterface|null $currentUser
     * @return JsonResponse
     */
    #[Route('/api/getweatherall/{city?}', name: 'app_api_getweather_all', methods: ['GET'])]
    public function getWeatherAll(WeatherService $weatherService, ?string $city, #[CurrentUser] ?UserInterface $currentUser): JsonResponse
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
