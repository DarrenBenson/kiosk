<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

define('WEATHER_API_KEY', '0df11eccd2946a27007d56bec0516152');
define('DIDCOT_LAT', '51.6095');
define('DIDCOT_LON', '-1.2401');

/**
 * Fetches current weather data for Didcot
 * @return array Weather data
 */
function getCurrentWeather() {
    $url = sprintf(
        'https://api.openweathermap.org/data/2.5/weather?lat=%s&lon=%s&units=metric&appid=%s',
        DIDCOT_LAT,
        DIDCOT_LON,
        WEATHER_API_KEY
    );
    
    $response = file_get_contents($url);
    return json_decode($response, true);
}

/**
 * Fetches hourly forecast for Didcot
 * @return array Forecast data
 */
function getHourlyForecast() {
    $url = sprintf(
        'https://api.openweathermap.org/data/2.5/forecast?lat=%s&lon=%s&units=metric&appid=%s',
        DIDCOT_LAT,
        DIDCOT_LON,
        WEATHER_API_KEY
    );
    
    $response = file_get_contents($url);
    $data = json_decode($response, true);
    
    // Get next 8 hours of forecast
    return array_slice($data['list'], 0, 8);
}

// Combine current weather and forecast
$weatherData = [
    'current' => getCurrentWeather(),
    'hourly' => getHourlyForecast()
];

echo json_encode($weatherData); 