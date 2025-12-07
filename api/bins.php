<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Load configuration
require_once __DIR__ . '/../config.php';

define('BIN_UPRN', getConfig('BIN_UPRN', ''));
define('CACHE_FILE', __DIR__ . '/../cache/bins_cache.json');
define('CACHE_DURATION', 21600); // 6 hours in seconds
define('BINZONE_URL', 'https://eform.southoxon.gov.uk/ebase/BINZONE_DESKTOP.eb');

/**
 * Fetches bin collection data from South Oxfordshire's BinZone API
 * @return array|null Array of bin collections or null on failure
 */
function fetchFromBinZone() {
    if (empty(BIN_UPRN)) {
        return null;
    }

    $cookie = 'SVBINZONE=SOUTH%3AUPRN%40' . urlencode(BIN_UPRN);
    $url = BINZONE_URL . '?SOVA_TAG=SOUTH&ebd=0';

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_COOKIE => $cookie,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($httpCode !== 200 || !$response) {
        error_log("BinZone API error: HTTP $httpCode - $error");
        return null;
    }

    return parseHtmlResponse($response);
}

/**
 * Parse HTML response from BinZone API
 * Extracts collection dates and bin types from div elements with class "binextra"
 * @param string $html HTML response from BinZone
 * @return array|null Array of collections or null if parsing fails
 */
function parseHtmlResponse($html) {
    $collections = [];

    // Use regex to find all divs with class="binextra"
    preg_match_all('/<div[^>]*class="binextra"[^>]*>([^<]+)<\/div>/i', $html, $matches);

    if (empty($matches[1])) {
        error_log("BinZone API: No binextra divs found in response");
        return null;
    }

    foreach ($matches[1] as $content) {
        $content = trim($content);

        // Parse format: "DD Mon - BIN TYPE" or similar
        if (preg_match('/^(\d{1,2}\s+\w+)\s*-\s*(.+)$/i', $content, $parsed)) {
            $dateStr = trim($parsed[1]);
            $binType = trim($parsed[2]);

            // Convert relative date to timestamp (adds current year)
            $year = date('Y');
            $dateObj = DateTime::createFromFormat('j M Y', "$dateStr $year");

            // If date has passed, assume next year
            if ($dateObj && $dateObj < new DateTime()) {
                $dateObj = DateTime::createFromFormat('j M Y', "$dateStr " . ($year + 1));
            }

            if ($dateObj) {
                $collections[] = [
                    'date' => $dateObj->format('D j M'),
                    'timestamp' => $dateObj->getTimestamp(),
                    'type' => $binType
                ];
            }
        }
    }

    // Sort by timestamp
    usort($collections, fn($a, $b) => $a['timestamp'] - $b['timestamp']);

    return empty($collections) ? null : $collections;
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
 * Get the next bin collection from a sorted list
 * @param array $collections Sorted array of collection dates
 * @return array Next collection or current if none in future
 */
function getNextCollection($collections) {
    $now = time();

    foreach ($collections as $collection) {
        if ($collection['timestamp'] >= $now) {
            return $collection;
        }
    }

    // If no future collections, return the last one
    return end($collections);
}

/**
 * Determines bin collection schedule
 * Uses cached data when available, fetches from BinZone when needed
 * Falls back to estimated schedule if API unavailable
 * @return array Bin collection data
 */
function getBinCollection() {
    // Try to get cached data first
    $cached = getCachedData();
    if ($cached) {
        return $cached;
    }

    // Try to fetch from BinZone API
    $collections = fetchFromBinZone();
    if ($collections) {
        $nextCollection = getNextCollection($collections);
        cacheData($nextCollection);
        return $nextCollection;
    }

    // Fallback to estimated schedule
    error_log('Warning: Using estimated bin collection schedule. Configure BIN_UPRN in .env for accurate data.');

    $currentWeek = date('W');
    $isEvenWeek = $currentWeek % 2 === 0;

    $today = new DateTime();
    $daysUntilMonday = (8 - $today->format('N')) % 7;
    if ($daysUntilMonday === 0 && $today->format('H') >= 6) {
        $daysUntilMonday = 7;
    }

    $nextCollection = clone $today;
    $nextCollection->modify("+{$daysUntilMonday} days");

    return [
        'date' => $nextCollection->format('D j M'),
        'estimated' => true,
        'type' => $isEvenWeek ? 'GREEN BIN & GREY BIN' : 'GREEN BIN & GARDEN WASTE BIN'
    ];
}

echo json_encode(getBinCollection()); 