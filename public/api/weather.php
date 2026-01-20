<?php
declare(strict_types=1);

/**
 * Weather API
 * Fetches current weather and hourly forecast from OpenWeatherMap
 */
define('KIOSK_APP', true);
require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

/**
 * Fetches current weather data
 * @return array<string, mixed> Weather data or error array
 */
function getCurrentWeather(): array {
    if (empty(WEATHER_API_KEY)) {
        return ['error' => 'Weather API key not configured'];
    }

    $url = sprintf(
        'https://api.openweathermap.org/data/2.5/weather?lat=%s&lon=%s&units=metric&appid=%s',
        urlencode(WEATHER_LAT),
        urlencode(WEATHER_LON),
        urlencode(WEATHER_API_KEY)
    );

    $response = @file_get_contents($url);
    if ($response === false) {
        return ['error' => 'Failed to fetch weather data'];
    }

    $data = json_decode($response, true);
    return is_array($data) ? $data : ['error' => 'Invalid weather data'];
}

/**
 * Fetches hourly forecast
 * @return array<int, array<string, mixed>> Forecast data (next 8 periods) or empty array on failure
 */
function getHourlyForecast(): array {
    if (empty(WEATHER_API_KEY)) {
        return [];
    }

    $url = sprintf(
        'https://api.openweathermap.org/data/2.5/forecast?lat=%s&lon=%s&units=metric&appid=%s',
        urlencode(WEATHER_LAT),
        urlencode(WEATHER_LON),
        urlencode(WEATHER_API_KEY)
    );

    $response = @file_get_contents($url);
    if ($response === false) {
        return [];
    }

    $data = json_decode($response, true);

    // Return next 8 forecast periods
    if (isset($data['list']) && is_array($data['list'])) {
        return array_slice($data['list'], 0, 8);
    }

    return [];
}

// Combine current weather and forecast
$weatherData = [
    'current' => getCurrentWeather(),
    'hourly' => getHourlyForecast(),
    'location' => WEATHER_LOCATION
];

echo json_encode($weatherData, JSON_THROW_ON_ERROR);
