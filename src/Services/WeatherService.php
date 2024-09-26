<?php

require_once '../../vendor/autoload.php';
require_once '../Config/config.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class WeatherService
{
    private $geoUrl;
    private $weatherUrl;
    private $client;
    private $cacheService;
    private $loggerService;
    private $apiKey;

    public function __construct(CacheService $cacheService, LoggerService $loggerService)
    {
        $this->geoUrl = "http://api.openweathermap.org/geo/1.0/direct";
        $this->weatherUrl = "https://api.openweathermap.org/data/2.5/forecast";
        $this->client = new Client();
        $this->cacheService = $cacheService;
        $this->loggerService = $loggerService;
        $this->apiKey = API_KEY;
    }

    public function getWeatherData($city)
    {
        $cachedData = $this->cacheService->getCachedData($city);
        if ($cachedData) {
            $this->loggerService->logInfo("Cache hit for city: $city");
            return $cachedData;
        }

        try {
            $geoData = $this->getGeoData($city);
            if (empty($geoData)) {
                throw new Exception('City "' . $city . '" not found. Please check the name and try again.');
            }

            $latitude = $geoData[0]['lat'];
            $longitude = $geoData[0]['lon'];

            $weatherResponse = $this->client->request('GET', $this->weatherUrl, [
                'query' => [
                    'lat' => $latitude,
                    'lon' => $longitude,
                    'units' => 'metric',
                    'appid' => $this->apiKey
                ]
            ]);

            $weatherData = json_decode($weatherResponse->getBody(), true);

            if (!isset($weatherData['list']) || empty($weatherData['list'])) {
                throw new Exception('No weather data available for this city.');
            }

            $this->cacheService->cacheData($city, $weatherData);
            $this->loggerService->logInfo("API response received and cached for city: $city");

            return $weatherData;
        } catch (RequestException $e) {
            $this->loggerService->logError($e->getMessage());
            throw new Exception('Error fetching data from the API.');
        } catch (Exception $e) {
            $this->loggerService->logError($e->getMessage());
            throw $e;
        }
    }

    private function getGeoData($city)
    {
        try {
            $geoResponse = $this->client->request('GET', $this->geoUrl, [
                'query' => [
                    'q' => $city,
                    'limit' => 1,
                    'appid' => $this->apiKey
                ]
            ]);
            return json_decode($geoResponse->getBody(), true);
        } catch (RequestException $e) {
            $this->loggerService->logError("GeoData Request Error: " . $e->getMessage());
            throw $e;
        }
    }

    public function cToFa($temp)
    {
        return round($temp * 9 / 5 + 32);
    }

    public function getTodayWeather($weatherData)
    {
        return $weatherData['list'][0];
    }

    public function getTomorrowWeather($weatherData)
    {
        return $weatherData['list'][8];
    }
}
