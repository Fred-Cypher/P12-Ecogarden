<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class WeatherService
{
    private HttpClientInterface $httpClient;
    private string $apiKey;

    public function __construct(HttpClientInterface $httpClient, string $weatherApiKey)
    {
        $this->httpClient = $httpClient;
        $this->apiKey = $weatherApiKey;
    }

    private function getCoordinates(string $cityName): ?array
    {
//        dd($this->apiKey);
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
        $coordinates = $this->getCoordinates($cityName);

        if (!$coordinates) {
            return null;
        }

        return $this->getCurrentWeather($coordinates['latitude'], $coordinates['longitude']);
    }

}
