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
        $city = $city ?? $currentUser->getCity();

        try {
            $weatherData = $weatherService->getWeatherForCity($city);

            $weather = $weatherData['weather'];
            $fetchedAt = $weatherData['fetched_at'];

            $dateTime = (new \DateTime())->setTimestamp($weather['dt'])->format('d/m/Y H:i:s');

            $meteo = [
                'Lieu de la sonde météo' => $weather['name'],
                'Qualité du ciel' => $weather['weather'][0]['description'],
                'Température' => $weather['main']['temp'],
                'Taux d\'humidité' => $weather['main']['humidity'],
                'Date et heure' => $dateTime,
                'Heure de récupération (cache/local)' => $fetchedAt,
            ];

            return new JsonResponse($meteo, Response::HTTP_OK);

        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
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

        $city = $city ?? $currentUser->getCity();

        try {
            $weather = $weatherService->getWeatherForCity($city);

            return new JsonResponse($weather, Response::HTTP_OK);

        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * @param WeatherService $weatherService
     * @return JsonResponse
     */
    #[Route('/api/clearweathercache', name: 'app_api_clear_weather_cache', methods: ['DELETE'])]
    public function clearWeatherCache(WeatherService $weatherService): JsonResponse
    {
        $weatherService->clearWeatherCache();
        return new JsonResponse(['message' => 'Cache météo vidé'], Response::HTTP_OK);
    }

}
