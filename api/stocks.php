<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Load configuration
require_once __DIR__ . '/../config.php';

// API credentials
define('API_KEY', getConfig('ALPACA_API_KEY', ''));
define('API_SECRET', getConfig('ALPACA_API_SECRET', ''));

/**
 * Fetches current GBP/USD exchange rate
 * @return float Exchange rate
 */
function getGBPRate() {
    $url = "https://api.exchangerate-api.com/v4/latest/USD";
    $response = file_get_contents($url);
    $data = json_decode($response, true);
    return $data['rates']['GBP'];
}

/**
 * Fetches stock quotes from Alpaca API and converts to specified currency
 * @param string|null $symbols Comma-separated stock symbols (e.g., 'AAPL,MSFT,GOOGL')
 * @param string $currency Currency to convert to (default: 'GBP')
 * @return array Simplified stock price data
 */
function getStockQuotes($symbols = null, $currency = 'GBP') {
    // Check if API keys are configured
    if (empty(API_KEY) || empty(API_SECRET)) {
        return ['error' => 'Stock API keys not configured'];
    }

    // Default symbols if none provided
    if (!$symbols) {
        $symbols = 'GOOGL';
    }

    $url = "https://data.alpaca.markets/v2/stocks/quotes/latest?symbols=" . urlencode($symbols);

    // Initialize cURL session
    $ch = curl_init($url);

    // Set cURL options
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'APCA-API-KEY-ID: ' . API_KEY,
            'APCA-API-SECRET-KEY: ' . API_SECRET,
            'Accept: application/json'
        ]
    ]);
    
    // Execute request
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    // Check for errors
    if (curl_errno($ch)) {
        http_response_code(500);
        return ['error' => 'Failed to fetch stock data: ' . curl_error($ch)];
    }
    
    curl_close($ch);
    
    // Handle response
    if ($httpCode !== 200) {
        http_response_code($httpCode);
        return ['error' => 'API request failed with status ' . $httpCode];
    }
    
    $data = json_decode($response, true);
    $exchangeRate = strtoupper($currency) !== 'USD' ? getExchangeRate($currency) : 1;
    
    // Simplify the output format
    $result = [];
    if (isset($data['quotes'])) {
        foreach ($data['quotes'] as $symbol => $quote) {
            if (isset($quote['ap'])) {
                $price = $quote['ap'] * $exchangeRate;
                $result[$symbol] = round($price, 2);
            }
        }
        // Sort by symbol alphabetically
        ksort($result);
    }
    
    return $result;
}

/**
 * Fetches exchange rate for specified currency against USD
 * @param string $currency Currency code (e.g., 'GBP', 'EUR')
 * @return float Exchange rate
 */
function getExchangeRate($currency) {
    $url = "https://api.exchangerate-api.com/v4/latest/USD";
    $response = file_get_contents($url);
    $data = json_decode($response, true);
    return $data['rates'][strtoupper($currency)] ?? 1;
}

// Get parameters from query
$requestedSymbols = isset($_GET['symbols']) ? $_GET['symbols'] : null;
$requestedCurrency = isset($_GET['currency']) ? $_GET['currency'] : 'GBP';

// Return stock data
echo json_encode(getStockQuotes($requestedSymbols, $requestedCurrency)); 