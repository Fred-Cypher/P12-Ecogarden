<?php

namespace App\Service;

use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class WeatherService
{
    private HttpClientInterface $httpClient;
    private string $apiKey;
    private TagAwareCacheInterface $cache;

    public function __construct(HttpClientInterface $httpClient, string $weatherApiKey, TagAwareCacheInterface $cache)
    {
        $this->httpClient = $httpClient;
        $this->apiKey = $weatherApiKey;
        $this->cache = $cache;
    }

    private function getCoordinates(string $cityName): ?array
    {
        $city = urlencode($cityName);

        $url = "https://api.openweathermap.org/geo/1.0/direct?q={$city}&limit=1&appid={$this->apiKey}";

        try{
            $response = $this->httpClient->request('GET', $url);
            $data = $response->toArray();

            if (!empty($data)) {
                return [
                    'latitude' => $data[0]['lat'],
                    'longitude' => $data[0]['lon'],
                ];
            }
        } catch (\Exception $e){
            return $response->getInfo('Pas de coordonnées pour cette ville, veuillez changer de localité');
        }

        return null;

    }

    private function getCurrentWeather(float $latitude, float $longitude): ?array
    {
        $url = sprintf(
            'https://api.openweathermap.org/data/2.5/weather?lat=%f&lon=%f&units=metric&lang=fr&appid=%s',
            $latitude,
            $longitude,
            $this->apiKey
        );


        try{
            $response = $this->httpClient->request('GET', $url);
            if ($response->getStatusCode() === 200) {
                return $response->toArray();
            }
        } catch (\Exception $e){
            return null;
        }

        return null;
    }

    public function getWeatherForCity(string $cityName): ?array
    {
        $cacheKey = 'weather_' . strtolower($cityName);

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($cityName) {
            $item->expiresAfter(3600);
            $item->tag(['weather']);
            echo ("Mise_en_cache_des_éléments_de_météo_pour_la_ville_:  $cityName");

            $coordinates = $this->getCoordinates($cityName);

            if (!$coordinates) {
                throw new \Exception("Impossible de trouver les coordonnées pour la ville " . $cityName);
            }

            return $this->getCurrentWeather($coordinates['latitude'], $coordinates['longitude']);
        });
    }

    public function clearWeatherCache(): void
    {
        $this->cache->invalidateTags(['weather']);
    }

}
