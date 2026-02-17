<?php
declare(strict_types=1);

/**
 * Bin Collection API
 * Fetches bin collection data from South Oxfordshire / Vale of White Horse Binzone service
 */
define('KIOSK_APP', true);
require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

date_default_timezone_set(TIMEZONE);

/**
 * Fetches bin collection data from cache or API
 * @return array<int, array<string, mixed>>|null Array of collection entries or null on failure
 */
function fetchBinzoneData(): ?array {
    if (!defined('BIN_UPRN') || empty(BIN_UPRN)) {
        return null;
    }

    if (!is_dir(CACHE_DIR)) {
        mkdir(CACHE_DIR, 0755, true);
    }

    $cronCacheFile = CACHE_DIR . 'bins_data.json';
    $apiCacheFile = CACHE_DIR . 'bins_binzone_' . md5(BIN_UPRN) . '.json';

    // Try cron-generated cache first (updated by host Python script)
    if (file_exists($cronCacheFile) && (time() - filemtime($cronCacheFile)) < BIN_CACHE_DURATION) {
        $content = file_get_contents($cronCacheFile);
        if ($content !== false) {
            $rawData = json_decode($content, true);
            if (is_array($rawData)) {
                return processRawCollections($rawData);
            }
        }
    }

    // Check API cache
    if (file_exists($apiCacheFile) && (time() - filemtime($apiCacheFile)) < BIN_CACHE_DURATION) {
        $content = file_get_contents($apiCacheFile);
        if ($content !== false) {
            $cached = json_decode($content, true);
            if (is_array($cached)) {
                return $cached;
            }
        }
    }

    // Try to fetch from Binzone API
    $council = defined('BIN_COUNCIL') ? BIN_COUNCIL : 'SOUTH';
    $collections = fetchBinzoneApi(BIN_UPRN, $council);

    if ($collections === null) {
        // Return stale cache if available
        foreach ([$cronCacheFile, $apiCacheFile] as $cacheFile) {
            if (file_exists($cacheFile)) {
                $content = file_get_contents($cacheFile);
                if ($content !== false) {
                    $data = json_decode($content, true);
                    if (is_array($data)) {
                        return $cacheFile === $cronCacheFile ? processRawCollections($data) : $data;
                    }
                }
            }
        }
        return null;
    }

    // Process and cache
    $processed = processRawCollections($collections);

    if (!empty($processed)) {
        file_put_contents($apiCacheFile, json_encode($processed, JSON_THROW_ON_ERROR));
    }

    return $processed;
}

/**
 * Processes raw collection data into full format
 * @param array<int, array{date: string, bins: string}> $collections Raw collection data
 * @return array<int, array<string, mixed>> Processed collection data
 */
function processRawCollections(array $collections): array {
    $processed = [];

    foreach ($collections as $item) {
        if (!isset($item['date'], $item['bins'])) {
            continue;
        }
        $date = parseCollectionDate($item['date']);
        if ($date !== null) {
            $processed[] = [
                'date' => $date,
                'dateFormatted' => date('D j M', strtotime($date)),
                'bins' => parseBinsFromText($item['bins']),
                'raw' => $item['bins']
            ];
        }
    }

    // Sort by date
    usort($processed, function (array $a, array $b): int {
        return strcmp($a['date'], $b['date']);
    });

    return $processed;
}

/**
 * Fetches bin data from Binzone API
 * Uses PHP cURL if available, falls back to Python helper
 * @return array<int, array{date: string, bins: string}>|null Array of raw collection entries or null on failure
 */
function fetchBinzoneApi(string $uprn, string $council): ?array {
    $url = 'https://eform.southoxon.gov.uk/ebase/BINZONE_DESKTOP.eb?SOVA_TAG=' . urlencode($council) . '&ebd=0';
    $cookieValue = $council . '%3AUPRN%40' . $uprn;

    $html = null;

    // Try PHP cURL first (works in Docker)
    if (function_exists('curl_init')) {
        $ch = curl_init();
        if ($ch !== false) {
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 15,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_COOKIEJAR => '',
                CURLOPT_COOKIEFILE => '',
                CURLOPT_COOKIE => 'SVBINZONE=' . $cookieValue,
                CURLOPT_HTTPHEADER => [
                    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36',
                    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    'Accept-Language: en-GB,en;q=0.9',
                ]
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if (is_string($response) && $httpCode === 200 && strpos($response, '403 Forbidden') === false) {
                $html = $response;
            }
        }
    }

    // Fallback to Python helper for local development
    if ($html === null) {
        $scriptPath = __DIR__ . '/../../tools/fetch_binzone.py';
        if (file_exists($scriptPath)) {
            $cmd = sprintf(
                'python3 %s %s %s 2>/dev/null',
                escapeshellarg($scriptPath),
                escapeshellarg($uprn),
                escapeshellarg($council)
            );
            $output = shell_exec($cmd);
            if (is_string($output)) {
                $data = json_decode($output, true);
                if (is_array($data) && !isset($data['error'])) {
                    return $data;
                }
            }
        }
        return null;
    }

    // Parse HTML for binextra elements
    return parseBinzoneHtml($html);
}

/**
 * Parses Binzone HTML response for collection data
 * @return array<int, array{date: string, bins: string}> Array of raw collection entries
 */
function parseBinzoneHtml(string $html): array {
    $collections = [];

    if (preg_match_all('/<div[^>]*class="[^"]*binextra[^"]*"[^>]*>(.*?)<\/div>/is', $html, $matches)) {
        foreach ($matches[1] as $content) {
            $text = strip_tags($content);
            $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
            $text = trim($text);

            if (preg_match('/^(.+?)\s*-\s*(.+)$/i', $text, $parts)) {
                $dateStr = trim($parts[1]);
                $binsStr = strtolower(trim($parts[2]));
                $dateStr = preg_replace('/Your usual collection day is different this week\s*/i', '', $dateStr) ?? $dateStr;

                $collections[] = [
                    'date' => $dateStr,
                    'bins' => $binsStr
                ];
            }
        }
    }

    return $collections;
}

/**
 * Parses a date string like "Friday 23 January" into Ymd format
 */
function parseCollectionDate(string $dateStr): ?string {
    // Add current year if not present
    if (!preg_match('/\d{4}/', $dateStr)) {
        $dateStr .= ' ' . date('Y');
    }

    $timestamp = strtotime($dateStr);

    if ($timestamp === false) {
        return null;
    }

    // If date is in the past, try next year
    $today = strtotime('today');
    if ($today !== false && $timestamp < $today) {
        $timestamp = strtotime($dateStr . ' +1 year');
        if ($timestamp === false) {
            return null;
        }
    }

    return date('Ymd', $timestamp);
}

/**
 * Determines which bins are due based on text description
 * @return array{green: bool, grey: bool, brown: bool} Associative array of bin types and whether they're due
 */
function parseBinsFromText(string $text): array {
    $bins = [
        'green' => false,
        'grey' => false,
        'brown' => false
    ];

    // Green bin (recycling)
    if (strpos($text, 'green') !== false || strpos($text, 'recycling') !== false) {
        $bins['green'] = true;
    }

    // Grey bin (general waste/rubbish)
    if (strpos($text, 'grey') !== false || strpos($text, 'gray') !== false || strpos($text, 'rubbish') !== false) {
        $bins['grey'] = true;
    }

    // Brown bin (garden waste)
    if (strpos($text, 'garden') !== false || strpos($text, 'brown') !== false) {
        $bins['brown'] = true;
    }

    return $bins;
}

/**
 * Gets the next bin collection information
 * @return array<string, mixed> Collection data including date, bins, and status
 */
function getBinCollection(): array {
    $today = date('Ymd');
    $todayDate = new DateTime();

    $collections = fetchBinzoneData();

    if (empty($collections)) {
        return [
            'error' => 'Unable to fetch bin collection data. Check UPRN in config.php.',
            'date' => date('D j M'),
            'bins' => ['green' => false, 'grey' => false, 'brown' => false],
            'nextCollection' => null,
            'isToday' => false,
            'daysUntil' => null
        ];
    }

    // Find the next collection (today or future)
    $nextCollection = null;
    foreach ($collections as $collection) {
        if ($collection['date'] >= $today) {
            $nextCollection = $collection;
            break;
        }
    }

    if ($nextCollection === null) {
        return [
            'error' => 'No upcoming collections found.',
            'date' => date('D j M'),
            'bins' => ['green' => false, 'grey' => false, 'brown' => false],
            'nextCollection' => null,
            'isToday' => false,
            'daysUntil' => null
        ];
    }

    // Calculate days until collection
    $collectionDate = DateTime::createFromFormat('Ymd', $nextCollection['date']);
    if ($collectionDate === false) {
        return [
            'error' => 'Invalid collection date.',
            'date' => date('D j M'),
            'bins' => ['green' => false, 'grey' => false, 'brown' => false],
            'nextCollection' => null,
            'isToday' => false,
            'daysUntil' => null
        ];
    }

    $diff = $todayDate->diff($collectionDate);
    $daysUntil = (int)$diff->format('%r%a');
    $isToday = ($daysUntil === 0);

    return [
        'date' => $nextCollection['dateFormatted'],
        'bins' => $nextCollection['bins'],
        'nextCollection' => $nextCollection['date'],
        'isToday' => $isToday,
        'daysUntil' => $daysUntil,
        'description' => $nextCollection['raw']
    ];
}

echo json_encode(getBinCollection(), JSON_THROW_ON_ERROR);
