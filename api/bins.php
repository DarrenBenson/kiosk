<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Load configuration
require_once __DIR__ . '/../config.php';

define('BIN_UPRN', getConfig('BIN_UPRN', ''));
define('CACHE_FILE', __DIR__ . '/../cache/bins_cache.json');
define('CACHE_DURATION', 21600); // 6 hours in seconds

/**
 * Fetches bin collection data from Vale of White Horse District Council
 * @return array|null Bin collection data or null on failure
 */
function fetchFromCouncil() {
    if (empty(BIN_UPRN)) {
        return null;
    }

    // Vale of White Horse uses a form-based system
    // The endpoint pattern is typically: https://www.whitehorsedc.gov.uk/...
    // For now, we'll need to implement the actual scraping or API call
    // This is a placeholder that will need the actual implementation

    $url = "https://www.whitehorsedc.gov.uk/api/bins/" . BIN_UPRN;

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_FOLLOWLOCATION => true
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200 && $response) {
        $data = json_decode($response, true);
        if ($data) {
            return $data;
        }
    }

    return null;
}

/**
 * Parse bin collection data into standard format
 * @param array $rawData Raw data from council API
 * @return array Formatted bin collection data
 */
function parseCollectionData($rawData) {
    // This will need to be customized based on actual API response
    // For now, return a standard format
    $collections = [];

    foreach ($rawData as $collection) {
        $collections[] = [
            'date' => $collection['date'],
            'type' => $collection['type'],
            'bins' => $collection['bins']
        ];
    }

    return $collections;
}

/**
 * Get cached bin collection data
 * @return array|null Cached data or null if not available/expired
 */
function getCachedData() {
    if (!file_exists(CACHE_FILE)) {
        return null;
    }

    $cacheData = json_decode(file_get_contents(CACHE_FILE), true);
    if (!$cacheData || !isset($cacheData['timestamp'])) {
        return null;
    }

    // Check if cache is still valid
    if (time() - $cacheData['timestamp'] > CACHE_DURATION) {
        return null;
    }

    return $cacheData['data'];
}

/**
 * Save bin collection data to cache
 * @param array $data Data to cache
 */
function cacheData($data) {
    $cacheDir = dirname(CACHE_FILE);
    if (!is_dir($cacheDir)) {
        mkdir($cacheDir, 0755, true);
    }

    $cacheData = [
        'timestamp' => time(),
        'data' => $data
    ];

    file_put_contents(CACHE_FILE, json_encode($cacheData));
}

/**
 * Get next bin collection information
 * @return array Next collection data
 */
function getNextCollection($collections) {
    $now = new DateTime();

    foreach ($collections as $collection) {
        $collectionDate = new DateTime($collection['date']);
        if ($collectionDate >= $now) {
            return $collection;
        }
    }

    // If no future collections, return the last one with a note
    return end($collections);
}

/**
 * Determines bin collection schedule
 * Uses cached data when available, fetches from council when needed
 * Falls back to estimated schedule if API unavailable
 * @return array Bin collection data
 */
function getBinCollection() {
    // Try to get cached data first
    $cached = getCachedData();
    if ($cached) {
        return $cached;
    }

    // Try to fetch from council
    $councilData = fetchFromCouncil();
    if ($councilData) {
        $collections = parseCollectionData($councilData);
        $nextCollection = getNextCollection($collections);
        cacheData($nextCollection);
        return $nextCollection;
    }

    // Fallback to estimated schedule based on typical Vale of White Horse pattern
    // Note: This is an approximation and should be replaced with actual data
    error_log('Warning: Using estimated bin collection schedule. Configure BIN_UPRN in .env for accurate data.');

    $currentWeek = date('W');
    $isEvenWeek = $currentWeek % 2 === 0;

    // Get next collection day (assuming Monday collections)
    $today = new DateTime();
    $daysUntilMonday = (8 - $today->format('N')) % 7;
    if ($daysUntilMonday === 0 && $today->format('H') >= 6) {
        $daysUntilMonday = 7; // If it's Monday after 6 AM, next Monday
    }

    $nextCollection = clone $today;
    $nextCollection->modify("+{$daysUntilMonday} days");

    return [
        'date' => $nextCollection->format('D j M'),
        'estimated' => true,
        'bins' => [
            'green' => true, // Recycling typically every week
            'grey' => $isEvenWeek,
            'brown' => !$isEvenWeek
        ]
    ];
}

echo json_encode(getBinCollection()); 