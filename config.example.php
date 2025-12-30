<?php
/**
 * Kiosk Display Configuration
 *
 * Copy this file to config.php and fill in your API keys and settings.
 * config.php is excluded from git to protect your credentials.
 */

// Prevent direct access
if (!defined('KIOSK_APP')) {
    die('Direct access not permitted');
}

// =============================================================================
// WEATHER API (OpenWeatherMap)
// Get your free API key at: https://openweathermap.org/api
// =============================================================================
define('WEATHER_API_KEY', '');
define('WEATHER_LAT', '51.6095');  // Didcot latitude
define('WEATHER_LON', '-1.2401');  // Didcot longitude
define('WEATHER_LOCATION', 'Didcot, UK');

// =============================================================================
// STOCK API (Alpaca Markets)
// Get your API keys at: https://alpaca.markets/
// =============================================================================
define('ALPACA_API_KEY', '');
define('ALPACA_API_SECRET', '');
define('DEFAULT_STOCK_SYMBOLS', 'AAPL,GOOGL,MSFT,TSLA,META,NOW,NVDA');
define('DEFAULT_CURRENCY', 'GBP');

// =============================================================================
// BIN COLLECTION (South Oxfordshire District Council)
//
// 1. Find your calendar type (S1 or S2) and collection day at:
//    https://www.southoxon.gov.uk/south-oxfordshire-district-council/recycling-rubbish-and-waste/when-is-your-collection-day/
//
// 2. Get calendar IDs from:
//    https://www.southoxon.gov.uk/south-oxfordshire-district-council/recycling-rubbish-and-waste/when-is-your-collection-day/waste-collections-calendar/add-your-waste-calendar-to-google-calendar-or-ical/
//
// The calendar ID is the part before @group.calendar.google.com in the iCal URL
// =============================================================================
define('BIN_CALENDAR_TYPE', 'S2');  // S1 or S2
define('BIN_COLLECTION_DAY', 'Friday');  // Monday, Tuesday, Wednesday, Thursday, or Friday

// Calendar IDs for S1 schedule (fill in the one matching your collection day)
define('BIN_CALENDAR_ID_S1_MONDAY', 'qfnbte8aomgagtu3svvpkov00k@group.calendar.google.com');
define('BIN_CALENDAR_ID_S1_TUESDAY', '');
define('BIN_CALENDAR_ID_S1_WEDNESDAY', '');
define('BIN_CALENDAR_ID_S1_THURSDAY', '');
define('BIN_CALENDAR_ID_S1_FRIDAY', '');

// Calendar IDs for S2 schedule (fill in the one matching your collection day)
define('BIN_CALENDAR_ID_S2_MONDAY', '');
define('BIN_CALENDAR_ID_S2_TUESDAY', '');
define('BIN_CALENDAR_ID_S2_WEDNESDAY', '');
define('BIN_CALENDAR_ID_S2_THURSDAY', '');
define('BIN_CALENDAR_ID_S2_FRIDAY', '');

// =============================================================================
// APPLICATION SETTINGS
// =============================================================================
define('TIMEZONE', 'Europe/London');
define('CACHE_DIR', __DIR__ . '/cache/');
define('BIN_CACHE_DURATION', 86400);    // 24 hours in seconds
define('WEATHER_CACHE_DURATION', 300);  // 5 minutes in seconds
define('EXCHANGE_CACHE_DURATION', 3600); // 1 hour in seconds
