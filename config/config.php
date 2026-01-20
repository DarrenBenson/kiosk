<?php
declare(strict_types=1);

/**
 * Kiosk Display Configuration
 *
 * Configuration priority:
 * 1. config.local.php (if exists) - for server-specific hardcoded values
 * 2. Environment variables - for Docker deployments
 * 3. Default values
 *
 * For standalone: Create config.local.php with your values (gitignored)
 * For Docker: Set environment variables in your container
 */

// Prevent direct access
if (!defined('KIOSK_APP')) {
    die('Direct access not permitted');
}

// Load local config override if it exists (gitignored, for server-specific values)
$localConfig = __DIR__ . '/config.local.php';
if (file_exists($localConfig)) {
    require_once $localConfig;
    return; // Skip the rest if local config is loaded
}

// =============================================================================
// BRANDING
// =============================================================================
define('SITE_TITLE', getenv('SITE_TITLE') ?: 'Kiosk Display');
define('LOGO_LEFT', getenv('LOGO_LEFT') ?: '');           // Path to left logo (optional)
define('LOGO_RIGHT', getenv('LOGO_RIGHT') ?: '');         // Path to right logo (optional)
define('NEWS_RSS_URL', getenv('NEWS_RSS_URL') ?: 'https://feeds.bbci.co.uk/news/uk/rss.xml');

// =============================================================================
// DISPLAY SETTINGS
// =============================================================================
define('DISPLAY_CURRENCY', getenv('DISPLAY_CURRENCY') ?: 'GBP');
define('LOCALE', getenv('LOCALE') ?: 'en-GB');
define('BINS_ENABLED', filter_var(getenv('BINS_ENABLED') ?: 'true', FILTER_VALIDATE_BOOLEAN));

// =============================================================================
// WEATHER API (OpenWeatherMap)
// =============================================================================
define('WEATHER_API_KEY', getenv('WEATHER_API_KEY') ?: '');
define('WEATHER_LAT', getenv('WEATHER_LAT') ?: '51.6095');
define('WEATHER_LON', getenv('WEATHER_LON') ?: '-1.2401');
define('WEATHER_LOCATION', getenv('WEATHER_LOCATION') ?: 'Didcot, UK');

// =============================================================================
// STOCK API (Alpaca Markets)
// =============================================================================
define('ALPACA_API_KEY', getenv('ALPACA_API_KEY') ?: '');
define('ALPACA_API_SECRET', getenv('ALPACA_API_SECRET') ?: '');
define('DEFAULT_STOCK_SYMBOLS', getenv('STOCK_SYMBOLS') ?: 'AAPL,GOOGL,MSFT,AMZN,META,AMD,NVDA');
define('DEFAULT_CURRENCY', getenv('STOCK_CURRENCY') ?: 'GBP');

// UK Stocks (Alpha Vantage)
define('ALPHA_VANTAGE_API_KEY', getenv('ALPHA_VANTAGE_API_KEY') ?: '');
define('UK_STOCK_SYMBOLS', getenv('UK_STOCK_SYMBOLS') ?: 'INCH.LON');

// =============================================================================
// BIN COLLECTION (South Oxfordshire / Vale of White Horse)
// =============================================================================
define('BIN_UPRN', getenv('BIN_UPRN') ?: '');
define('BIN_COUNCIL', getenv('BIN_COUNCIL') ?: 'SOUTH');

// =============================================================================
// APPLICATION SETTINGS
// =============================================================================
define('TIMEZONE', getenv('TIMEZONE') ?: 'Europe/London');
define('CACHE_DIR', __DIR__ . '/../cache/');
define('BIN_CACHE_DURATION', 86400);    // 24 hours
define('WEATHER_CACHE_DURATION', 300);  // 5 minutes
define('EXCHANGE_CACHE_DURATION', 3600); // 1 hour
