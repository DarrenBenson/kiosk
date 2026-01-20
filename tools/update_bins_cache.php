#!/usr/bin/env php
<?php
/**
 * Updates the bin collection cache file.
 * Run via cron: 0 * * * * /usr/bin/php /path/to/update_bins_cache.php
 *
 * Reads config from environment variables or config file.
 */

function getConfig() {
    $uprn = getenv('BIN_UPRN') ?: '';
    $council = getenv('BIN_COUNCIL') ?: 'SOUTH';

    // Try to read from PHP config if env vars not set
    if (empty($uprn)) {
        $configPath = dirname(__DIR__) . '/config/config.local.php';
        if (file_exists($configPath)) {
            $content = file_get_contents($configPath);
            if (preg_match("/define\s*\(\s*'BIN_UPRN'\s*,\s*'([^']+)'/", $content, $match)) {
                $uprn = $match[1];
            }
            if (preg_match("/define\s*\(\s*'BIN_COUNCIL'\s*,\s*'([^']+)'/", $content, $match)) {
                $council = $match[1];
            }
        }
    }

    return [$uprn, $council];
}

function fetchBinzone($uprn, $council = 'SOUTH') {
    $url = 'https://eform.southoxon.gov.uk/ebase/BINZONE_DESKTOP.eb?SOVA_TAG=' . $council . '&ebd=0';
    $cookieValue = $council . '%3AUPRN%40' . $uprn;

    // Try cURL if available
    if (function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_COOKIEFILE => '',
            CURLOPT_COOKIE => 'SVBINZONE=' . $cookieValue,
            CURLOPT_HTTPHEADER => [
                'User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language: en-GB,en;q=0.9',
            ]
        ]);

        $html = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($html !== false && $httpCode === 200 && strpos($html, '403 Forbidden') === false) {
            return $html;
        }
        fwrite(STDERR, "cURL failed with HTTP $httpCode, trying file_get_contents...\n");
    }

    // Fallback to file_get_contents with stream context
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => [
                'User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language: en-GB,en;q=0.9',
                'Cookie: SVBINZONE=' . $cookieValue
            ],
            'timeout' => 15,
            'follow_location' => true,
            'max_redirects' => 10
        ],
        'ssl' => [
            'verify_peer' => true,
            'verify_peer_name' => true
        ]
    ]);

    $html = @file_get_contents($url, false, $context);

    if ($html === false) {
        $error = error_get_last();
        fwrite(STDERR, "Error fetching data: " . ($error['message'] ?? 'Unknown error') . "\n");
        return null;
    }

    // Debug output
    fwrite(STDERR, "Response length: " . strlen($html) . "\n");
    if (isset($http_response_header)) {
        fwrite(STDERR, "Response headers:\n");
        foreach ($http_response_header as $h) {
            fwrite(STDERR, "  $h\n");
        }
    }

    if (strpos($html, '403 Forbidden') !== false) {
        fwrite(STDERR, "Error: Got 403 Forbidden response\n");
        return null;
    }

    // Check if we got redirected to a different page
    if (strpos($html, 'binextra') === false) {
        fwrite(STDERR, "Warning: Response doesn't contain binextra elements\n");
        fwrite(STDERR, "First 500 chars: " . substr($html, 0, 500) . "\n");
    }

    return $html;
}

function parseBinextra($html) {
    $collections = [];

    if (preg_match_all('/<div[^>]*class="[^"]*binextra[^"]*"[^>]*>(.*?)<\/div>/is', $html, $matches)) {
        foreach ($matches[1] as $content) {
            $text = strip_tags($content);
            $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
            $text = trim($text);

            if (preg_match('/^(.+?)\s*-\s*(.+)$/i', $text, $parts)) {
                $dateStr = trim($parts[1]);
                $binsStr = strtolower(trim($parts[2]));
                $dateStr = preg_replace('/Your usual collection day is different this week\s*/i', '', $dateStr);

                $collections[] = [
                    'date' => $dateStr,
                    'bins' => $binsStr
                ];
            }
        }
    }

    return $collections;
}

// Main execution
list($uprn, $council) = getConfig();

if (empty($uprn)) {
    fwrite(STDERR, "Error: BIN_UPRN not configured\n");
    exit(1);
}

echo "Fetching bin data for UPRN: $uprn, Council: $council\n";

$html = fetchBinzone($uprn, $council);

if ($html === null) {
    fwrite(STDERR, "Error: Failed to fetch data\n");
    exit(1);
}

$collections = parseBinextra($html);

if (empty($collections)) {
    fwrite(STDERR, "Error: No collections found in response\n");
    exit(1);
}

// Write cache file
$cacheDir = dirname(__DIR__) . '/cache';
if (!is_dir($cacheDir)) {
    mkdir($cacheDir, 0755, true);
}

$cacheFile = $cacheDir . '/bins_data.json';
file_put_contents($cacheFile, json_encode($collections, JSON_PRETTY_PRINT));

echo "Updated $cacheFile with " . count($collections) . " collections\n";
foreach ($collections as $c) {
    echo "  - {$c['date']}: {$c['bins']}\n";
}
