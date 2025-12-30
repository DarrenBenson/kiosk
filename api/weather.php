<?php
/**
 * Weather API
 * Fetches current weather and hourly forecast from OpenWeatherMap
 */
define('KIOSK_APP', true);
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

/**
 * Fetches current weather data
 * @return array Weather data or error array
 */
function getCurrentWeather() {
    if (empty(WEATHER_API_KEY)) {
        return ['error' => 'Weather API key not configured'];
    }

    $url = sprintf(
        'https://api.openweathermap.org/data/2.5/weather?lat=%s&lon=%s&units=metric&appid=%s',
        WEATHER_LAT,
        WEATHER_LON,
        WEATHER_API_KEY
    );

    $response = @file_get_contents($url);
    if ($response === false) {
        return ['error' => 'Failed to fetch weather data'];
    }

    return json_decode($response, true);
}

/**
 * Fetches hourly forecast
 * @return array Forecast data (next 8 periods) or empty array on failure
 */
function getHourlyForecast() {
    if (empty(WEATHER_API_KEY)) {
        return [];
    }

    $url = sprintf(
        'https://api.openweathermap.org/data/2.5/forecast?lat=%s&lon=%s&units=metric&appid=%s',
        WEATHER_LAT,
        WEATHER_LON,
        WEATHER_API_KEY
    );

    $response = @file_get_contents($url);
    if ($response === false) {
        return [];
    }

    $data = json_decode($response, true);

    // Return next 8 forecast periods
    return isset($data['list']) ? array_slice($data['list'], 0, 8) : [];
}

// Combine current weather and forecast
$weatherData = [
    'current' => getCurrentWeather(),
    'hourly' => getHourlyForecast(),
    'location' => WEATHER_LOCATION
];

echo json_encode($weatherData);
