<?php
declare(strict_types=1);

define('KIOSK_APP', true);

// Check for config file
if (!file_exists(__DIR__ . '/../config/config.php')) {
    die('Configuration file not found. Please copy config/config.example.php to config/config.local.php and configure your settings.');
}

require_once __DIR__ . '/../config/config.php';
date_default_timezone_set(defined('TIMEZONE') ? TIMEZONE : 'Europe/London');

/**
 * Gets a configuration value with fallback
 */
function getConfig(string $name, string $default = ''): string {
    if (defined($name)) {
        $value = constant($name);
        return is_string($value) && $value !== '' ? $value : $default;
    }
    return $default;
}

/**
 * Gets a boolean configuration value with fallback
 */
function getConfigBool(string $name, bool $default = true): bool {
    return defined($name) ? (bool)constant($name) : $default;
}

// Get configuration values
$siteTitle = getConfig('SITE_TITLE', 'Kiosk Display');
$logoLeft = getConfig('LOGO_LEFT', '');
$logoRight = getConfig('LOGO_RIGHT', '');
$newsRssUrl = getConfig('NEWS_RSS_URL', 'https://feeds.bbci.co.uk/news/uk/rss.xml');
$weatherLocation = getConfig('WEATHER_LOCATION', 'Weather');
$displayCurrency = getConfig('DISPLAY_CURRENCY', 'GBP');
$locale = getConfig('LOCALE', 'en-GB');
$binsEnabled = getConfigBool('BINS_ENABLED', true);
$stockSymbols = getConfig('DEFAULT_STOCK_SYMBOLS', 'AAPL,GOOGL,MSFT,AMZN,META,AMD,NVDA');
$ukStockSymbols = getConfig('UK_STOCK_SYMBOLS', '');
$weatherApiKey = getConfig('WEATHER_API_KEY', '');
$alpacaApiKey = getConfig('ALPACA_API_KEY', '');
$binUprn = getConfig('BIN_UPRN', '');

// Feature availability based on config
$weatherEnabled = !empty($weatherApiKey);
$stocksEnabled = !empty($alpacaApiKey);
$binsEnabled = $binsEnabled && !empty($binUprn);  // Requires both flag AND uprn

// Currency symbols
$currencySymbols = ['GBP' => '£', 'USD' => '$', 'EUR' => '€'];
$currencySymbol = $currencySymbols[$displayCurrency] ?? '£';

/**
 * Fetches and sanitises news feed items with fallback content
 * @return array<int, array{title: string, description: string, link: string, pubDate: string}>
 */
function getNewsItems(string $rssUrl): array {
    $items = [];
    $xml = @simplexml_load_file($rssUrl);

    if ($xml === false) {
        error_log("Failed to load RSS feed: $rssUrl");
        return [
            [
                'title' => 'Welcome',
                'description' => 'Check back later for the latest news and updates.',
                'link' => '#',
                'pubDate' => date('D, d M Y H:i:s O')
            ]
        ];
    }

    foreach ($xml->channel->item as $item) {
        $items[] = [
            'title' => htmlspecialchars((string)$item->title, ENT_QUOTES, 'UTF-8'),
            'description' => htmlspecialchars((string)$item->description, ENT_QUOTES, 'UTF-8'),
            'link' => htmlspecialchars((string)$item->link, ENT_QUOTES, 'UTF-8'),
            'pubDate' => htmlspecialchars((string)$item->pubDate, ENT_QUOTES, 'UTF-8')
        ];
    }

    return $items;
}

/**
 * Renders images from content/4x3 directory for the slideshow
 */
function renderSlideshowImages(): void {
    $files = glob("content/4x3/*");

    if (!empty($files)) {
        foreach ($files as $index => $file) {
            echo '<div class="mySlides fade" style="background-image: url(\'' . htmlspecialchars($file) . '\');">';
            echo '<div class="numbertext">' . ($index + 1) . ' / ' . count($files) . '</div>';
            echo '</div>';
        }
    }
}

/**
 * Build list of finance items to display based on config
 * @return array<int, string>
 */
function getFinanceItems(string $stockSymbols, string $ukStockSymbols, bool $stocksEnabled): array {
    // Always show currency rates (work without API keys)
    $items = ['usd', 'eur', 'btc'];

    // Only add stocks if API key is configured
    if ($stocksEnabled) {
        // Add configured stock symbols
        $stocks = array_filter(array_map('trim', explode(',', strtolower($stockSymbols))));
        $items = array_merge($items, $stocks);

        // Add UK stocks if configured
        if (!empty($ukStockSymbols)) {
            $ukStocks = array_filter(array_map('trim', explode(',', $ukStockSymbols)));
            foreach ($ukStocks as $symbol) {
                // Use the part before the dot for the ID (e.g., 'inch' from 'INCH.LON')
                $id = strtolower(explode('.', $symbol)[0]);
                if (!in_array($id, $items)) {
                    $items[] = $id;
                }
            }
        }
    }

    return $items;
}

$newsItems = getNewsItems($newsRssUrl);
$financeItems = getFinanceItems($stockSymbols, $ukStockSymbols, $stocksEnabled);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="style/slideshow.css">
    <link rel="stylesheet" type="text/css" href="style/ticker.css">
    <link rel="stylesheet" type="text/css" href="style/finance.css">
    <link rel="stylesheet" type="text/css" href="style/weather.css">
    <link rel="stylesheet" type="text/css" href="style/bins.css">
    <link href="favicon.ico" rel="shortcut icon" type="image/x-icon">
    <script>
        // Pass configuration to JavaScript
        window.KIOSK_CONFIG = {
            stockSymbols: <?= json_encode($stockSymbols) ?>,
            ukStockSymbols: <?= json_encode($ukStockSymbols) ?>,
            displayCurrency: <?= json_encode($displayCurrency) ?>,
            currencySymbol: <?= json_encode($currencySymbol) ?>,
            locale: <?= json_encode($locale) ?>,
            stocksEnabled: <?= json_encode($stocksEnabled) ?>,
            binsEnabled: <?= json_encode($binsEnabled) ?>
        };
    </script>
    <script src="scripts/ticker.js"></script>
    <script src="scripts/slideshow.js"></script>
    <script src="scripts/finance.js"></script>
    <script src="scripts/weather.js"></script>
    <script src="scripts/bins.js"></script>
    <title><?= htmlspecialchars($siteTitle) ?></title>
</head>
<body>
    <div class="ticker-container">
        <div class="ticker-caption"><p>Breaking News</p></div>
        <ul>
            <?php foreach ($newsItems as $item): ?>
                <div><li><span><strong><?= $item['title'] ?></strong> - <?= $item['description'] ?></span></li></div>
            <?php endforeach; ?>
        </ul>
    </div>

    <div class="finance-container">
        <?php foreach ($financeItems as $item): ?>
            <div class="finance-data" id="<?= htmlspecialchars($item) ?>-data">
                <span class="finance-label">Loading...</span>
                <span class="finance-value"><?= htmlspecialchars(strtoupper($item)) ?></span>
            </div>
        <?php endforeach; ?>
        <div class="finance-data" id="datetime">
            <span class="finance-label">Loading...</span>
            <span class="finance-value">Loading...</span>
        </div>
    </div>

    <?php if (!empty($logoLeft) && file_exists($logoLeft)): ?>
        <img src="<?= htmlspecialchars($logoLeft) ?>" alt="<?= htmlspecialchars($siteTitle) ?>" class="titlelogo">
    <?php endif; ?>

    <div class="slideshow-container">
        <?php renderSlideshowImages(); ?>

        <?php if ($weatherEnabled): ?>
        <div class="weather-container">
            <div class="current-weather">
                <div class="weather-info">
                    <div class="location"><?= htmlspecialchars($weatherLocation) ?></div>
                    <div class="description" id="weather-desc">--</div>
                </div>
                <div class="temp-now">
                    <img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" alt="" class="weather-icon" id="current-icon">
                    <div class="temps">
                        <span id="current-temp">--°C</span>
                        <span class="feels-like">Feels like <span id="feels-like-temp">--°C</span></span>
                    </div>
                </div>
                <div class="sun-times">
                    <div class="sunrise">
                        <img src="images/sunrise.png" alt="Sunrise" class="sun-icon">
                        <span id="sunrise-time">--:--</span>
                    </div>
                    <div class="sunset">
                        <img src="images/sunset.png" alt="Sunset" class="sun-icon">
                        <span id="sunset-time">--:--</span>
                    </div>
                </div>
            </div>
            <div class="hourly-forecast">
                <?php for($i = 1; $i <= 8; $i++): ?>
                    <div class="forecast-hour">
                        <div class="time" id="hour-<?=$i?>">--:--</div>
                        <img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" alt="" class="weather-icon small" id="icon-<?=$i?>">
                        <div class="temp" id="temp-<?=$i?>">--°C</div>
                    </div>
                <?php endfor; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($binsEnabled): ?>
        <div class="bins-container">
            <div class="bins-header">
                <div class="bins-title">Next Collection</div>
                <div class="bin-date" id="bin-date">--</div>
            </div>
            <div class="bin-icons">
                <div class="bin" id="green-bin" data-type="green">
                    <span class="bin-icon">♻️</span>
                    <span class="bin-label">Recycling</span>
                </div>
                <div class="bin" id="grey-bin" data-type="grey">
                    <span class="bin-icon">🗑️</span>
                    <span class="bin-label">Rubbish</span>
                </div>
                <div class="bin" id="brown-bin" data-type="brown">
                    <span class="bin-icon">🌱</span>
                    <span class="bin-label">Garden</span>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($logoRight) && file_exists($logoRight)): ?>
            <img src="<?= htmlspecialchars($logoRight) ?>" alt="Logo" class="logo">
        <?php endif; ?>
    </div>
</body>
</html>
