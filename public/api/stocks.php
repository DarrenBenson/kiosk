<?php
declare(strict_types=1);

/**
 * Stock Prices API
 * Fetches stock quotes from Alpaca Markets and converts to specified currency
 */
define('KIOSK_APP', true);
require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

/**
 * Fetches exchange rate for specified currency against USD with caching
 */
function getExchangeRate(string $currency): float {
    if (!is_dir(CACHE_DIR)) {
        mkdir(CACHE_DIR, 0755, true);
    }

    $cacheFile = CACHE_DIR . 'exchange_rates.json';
    $currency = strtoupper($currency);

    // Check cache
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < EXCHANGE_CACHE_DURATION) {
        $cached = json_decode(file_get_contents($cacheFile), true);
        if (isset($cached['rates'][$currency])) {
            return (float)$cached['rates'][$currency];
        }
    }

    // Fetch fresh data
    $url = 'https://api.exchangerate-api.com/v4/latest/USD';
    $response = @file_get_contents($url);

    if ($response !== false) {
        file_put_contents($cacheFile, $response);
        $data = json_decode($response, true);
        return (float)($data['rates'][$currency] ?? 1);
    }

    // Try stale cache
    if (file_exists($cacheFile)) {
        $cached = json_decode(file_get_contents($cacheFile), true);
        return (float)($cached['rates'][$currency] ?? 1);
    }

    return 1.0;
}

/**
 * Fetches previous day's close prices from Alpaca API
 * @return array<string, float> Symbol => previous close price
 */
function getPreviousClose(string $symbols): array {
    $end = date('Y-m-d\TH:i:s\Z');
    $start = date('Y-m-d\TH:i:s\Z', strtotime('-7 days'));

    $url = 'https://data.alpaca.markets/v2/stocks/bars?symbols=' . urlencode($symbols)
         . '&timeframe=1Day&start=' . urlencode($start) . '&end=' . urlencode($end)
         . '&feed=iex';

    $ch = curl_init($url);
    if ($ch === false) {
        return [];
    }

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'APCA-API-KEY-ID: ' . ALPACA_API_KEY,
            'APCA-API-SECRET-KEY: ' . ALPACA_API_SECRET,
            'Accept: application/json'
        ],
        CURLOPT_TIMEOUT => 10
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_errno($ch);
    curl_close($ch);

    if ($curlError || $httpCode !== 200 || !is_string($response)) {
        error_log("Alpaca bars API failed: HTTP $httpCode");
        return [];
    }

    $data = json_decode($response, true);
    $result = [];

    if (isset($data['bars']) && is_array($data['bars'])) {
        foreach ($data['bars'] as $symbol => $bars) {
            $barCount = count($bars);
            if ($barCount >= 2) {
                $result[$symbol] = (float)$bars[$barCount - 2]['c'];
            } elseif ($barCount === 1) {
                $result[$symbol] = (float)$bars[0]['c'];
            }
        }
    }

    return $result;
}

/**
 * Fetches stock quotes from Alpaca API
 * @return array<string, mixed> Stock price data or error
 */
function getStockQuotes(?string $symbols = null, ?string $currency = null): array {
    $symbols = $symbols ?? DEFAULT_STOCK_SYMBOLS;
    $currency = $currency ?? DEFAULT_CURRENCY;

    if (empty(ALPACA_API_KEY) || empty(ALPACA_API_SECRET)) {
        return ['error' => 'Alpaca API credentials not configured in config.php'];
    }

    $url = 'https://data.alpaca.markets/v2/stocks/quotes/latest?symbols=' . urlencode($symbols);

    $ch = curl_init($url);
    if ($ch === false) {
        return ['error' => 'Failed to initialise cURL'];
    }

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'APCA-API-KEY-ID: ' . ALPACA_API_KEY,
            'APCA-API-SECRET-KEY: ' . ALPACA_API_SECRET,
            'Accept: application/json'
        ],
        CURLOPT_TIMEOUT => 10
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if (!is_string($response)) {
        return ['error' => 'Failed to fetch stock data: ' . $curlError];
    }

    if ($httpCode !== 200) {
        return ['error' => 'Alpaca API request failed with status ' . $httpCode];
    }

    $data = json_decode($response, true);
    $exchangeRate = strtoupper($currency) !== 'USD' ? getExchangeRate($currency) : 1.0;
    $previousClose = getPreviousClose($symbols);

    $result = [];
    if (isset($data['quotes']) && is_array($data['quotes'])) {
        foreach ($data['quotes'] as $symbol => $quote) {
            if (isset($quote['ap'])) {
                $currentPrice = round($quote['ap'] * $exchangeRate, 2);
                $prevClose = isset($previousClose[$symbol])
                    ? round($previousClose[$symbol] * $exchangeRate, 2)
                    : null;

                $result[$symbol] = [
                    'price' => $currentPrice,
                    'prevClose' => $prevClose
                ];
            }
        }
        ksort($result);
    }

    return $result;
}

/**
 * Fetches UK stock quotes from Alpha Vantage API
 * @return array<string, array{price: float, prevClose: float}> Stock price data
 */
function getUKStockQuotes(?string $symbols = null): array {
    $symbols = $symbols ?? (defined('UK_STOCK_SYMBOLS') ? UK_STOCK_SYMBOLS : '');

    if (empty($symbols) || empty(ALPHA_VANTAGE_API_KEY)) {
        return [];
    }

    $result = [];
    $symbolList = explode(',', $symbols);

    foreach ($symbolList as $symbol) {
        $symbol = trim($symbol);
        if (empty($symbol)) {
            continue;
        }

        $url = 'https://www.alphavantage.co/query?function=GLOBAL_QUOTE&symbol='
             . urlencode($symbol) . '&apikey=' . ALPHA_VANTAGE_API_KEY;

        $response = @file_get_contents($url);
        if ($response === false) {
            continue;
        }

        $data = json_decode($response, true);
        if (!isset($data['Global Quote']['05. price'])) {
            continue;
        }

        $quote = $data['Global Quote'];
        // LSE prices are in pence, convert to pounds
        $price = floatval($quote['05. price']) / 100;
        $prevClose = floatval($quote['08. previous close']) / 100;

        // Use short symbol (INCH instead of INCH.LON) for display
        $shortSymbol = explode('.', $symbol)[0];

        $result[$shortSymbol] = [
            'price' => round($price, 2),
            'prevClose' => round($prevClose, 2)
        ];
    }

    return $result;
}

/**
 * Validates and sanitises stock symbols input
 */
function sanitiseSymbols(?string $input): ?string {
    if ($input === null) {
        return null;
    }
    // Only allow alphanumeric, commas, dots, and hyphens
    $sanitised = preg_replace('/[^A-Za-z0-9,.\-]/', '', $input);
    return $sanitised !== '' ? $sanitised : null;
}

/**
 * Validates currency code
 */
function sanitiseCurrency(string $input): string {
    $allowed = ['GBP', 'USD', 'EUR'];
    $upper = strtoupper(trim($input));
    return in_array($upper, $allowed, true) ? $upper : DEFAULT_CURRENCY;
}

// Process request
$requestedSymbols = sanitiseSymbols($_GET['symbols'] ?? null);
$requestedCurrency = sanitiseCurrency($_GET['currency'] ?? DEFAULT_CURRENCY);
$includeUK = ($_GET['uk'] ?? '1') === '1';

$result = getStockQuotes($requestedSymbols, $requestedCurrency);

// Add UK stocks if requested
if ($includeUK && !isset($result['error'])) {
    $ukStocks = getUKStockQuotes();
    $result = array_merge($result, $ukStocks);
}

echo json_encode($result, JSON_THROW_ON_ERROR);
