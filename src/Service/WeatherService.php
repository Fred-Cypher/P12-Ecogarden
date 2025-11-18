<?php

namespace App\Service;

use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class WeatherService
{
    private HttpClientInterface $httpClient;
    private string $apiKey;
    private TagAwareCacheInterface $cache;

    private string $openWeatherUrl;

    public function __construct(HttpClientInterface $httpClient, string $weatherApiKey, TagAwareCacheInterface $cache)
    {
        $this->httpClient = $httpClient;
        $this->apiKey = $weatherApiKey;
        $this->cache = $cache;
        $this->openWeatherUrl = 'https://api.openweathermap.org';
    }

    /**
     * Retrieve geographical coordinates for a city
     * @param string $cityName
     * @return array|null
     */
    private function getCoordinates(string $cityName): ?array
    {
        $city = urlencode($cityName);

        $url = "{$this->openWeatherUrl}/geo/1.0/direct?q={$city}&limit=1&appid={$this->apiKey}";

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
            return null;
        }

        return null;

    }

    /*
     * Retrieve weather data using geographical coordinates
     */
    private function getCurrentWeather(float $latitude, float $longitude): ?array
    {
        $url = sprintf(
            "{$this->openWeatherUrl}/data/2.5/weather?lat=%f&lon=%f&units=metric&lang=fr&appid=%s",
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

    /**
     * Retrieve weather data using city name
     * @throws InvalidArgumentException
     */
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

            return [
                'weather' => $this->getCurrentWeather($coordinates['latitude'], $coordinates['longitude']),
                'fetched_at' => (new \DateTime())->format('d/m/Y H:i:s'),
            ];
        });
    }

    /**
     * Clear weather cache
     * @throws InvalidArgumentException
     */
    public function clearWeatherCache(): void
    {
        $this->cache->invalidateTags(['weather']);
    }

}
