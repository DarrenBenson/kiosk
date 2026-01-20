<?php
declare(strict_types=1);

/**
 * Kiosk Display Configuration
 *
 * Copy this file to config.local.php and fill in your settings.
 * config.local.php is excluded from git to protect your credentials.
 *
 * For Docker deployments, you can also set environment variables instead.
 */

// Prevent direct access
if (!defined('KIOSK_APP')) {
    die('Direct access not permitted');
}

// =============================================================================
// BRANDING
// =============================================================================
define('SITE_TITLE', 'My Kiosk Display');       // Page title
define('LOGO_LEFT', '');                         // Path to left logo (optional, e.g. 'images/logo.png')
define('LOGO_RIGHT', '');                        // Path to right logo (optional)
define('NEWS_RSS_URL', 'https://feeds.bbci.co.uk/news/uk/rss.xml');

// =============================================================================
// DISPLAY SETTINGS
// =============================================================================
define('DISPLAY_CURRENCY', 'GBP');              // GBP, USD, or EUR
define('LOCALE', 'en-GB');                      // For date/number formatting
define('BINS_ENABLED', true);                   // Auto-disabled if BIN_UPRN is empty

// =============================================================================
// WEATHER API (OpenWeatherMap)
// Get your free API key at: https://openweathermap.org/api
// =============================================================================
define('WEATHER_API_KEY', '');
define('WEATHER_LAT', '51.5074');               // Your latitude (default: London)
define('WEATHER_LON', '-0.1278');               // Your longitude (default: London)
define('WEATHER_LOCATION', 'London, UK');       // Display name for location

// =============================================================================
// STOCK API (Alpaca Markets)
// Get your API keys at: https://alpaca.markets/
// =============================================================================
define('ALPACA_API_KEY', '');
define('ALPACA_API_SECRET', '');
define('DEFAULT_STOCK_SYMBOLS', 'AAPL,GOOGL,MSFT,TSLA,META,NVDA,AMD');
define('DEFAULT_CURRENCY', 'GBP');              // Currency to convert stock prices to

// UK Stocks (Alpha Vantage) - Optional
// Get your API key at: https://www.alphavantage.co/support/#api-key
define('ALPHA_VANTAGE_API_KEY', '');
define('UK_STOCK_SYMBOLS', '');                 // e.g. 'INCH.LON' for InchCape

// =============================================================================
// BIN COLLECTION (South Oxfordshire / Vale of White Horse)
// UK only - uses the Binzone service
//
// Find your UPRN (Unique Property Reference Number) at:
// https://www.findmyaddress.co.uk/
// =============================================================================
define('BIN_UPRN', '');                         // Your 11-12 digit UPRN
define('BIN_COUNCIL', 'SOUTH');                 // SOUTH or VALE

// =============================================================================
// APPLICATION SETTINGS
// =============================================================================
define('TIMEZONE', 'Europe/London');
define('CACHE_DIR', __DIR__ . '/cache/');
define('BIN_CACHE_DURATION', 86400);            // 24 hours in seconds
define('WEATHER_CACHE_DURATION', 300);          // 5 minutes in seconds
define('EXCHANGE_CACHE_DURATION', 3600);        // 1 hour in seconds
