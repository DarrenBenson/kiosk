<?php
/**
 * Fetches and sanitizes BBC news feed items with fallback content
 * @return array Array of news items with title, description, link, and date
 */
function getNewsItems() {
    $items = [];
    $xml = @simplexml_load_file("https://feeds.bbci.co.uk/news/uk/rss.xml");
    
    if ($xml === false) {
        error_log("Failed to load BBC RSS feed");
        return [
            [
                'title' => 'Welcome to Game Over Bar',
                'description' => 'Check back later for the latest news and updates.',
                'link' => '#',
                'pubDate' => date('D, d M Y H:i:s O')
            ],
            [
                'title' => 'Technical Difficulties',
                'description' => 'We are currently unable to load the latest news. Please try again later.',
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
 * Loads images from content/4x3 directory for the slideshow
 * @return array Array of image data with path, sequence number, and total count
 */
function getSlideshowImages() {
    $images = [];
    $files = glob("content/4x3/*");
    
    if (!empty($files)) {
        foreach ($files as $index => $file) {
            // Add inline style with background image to the slide div
            echo '<div class="mySlides fade" style="background-image: url(\'' . $file . '\');">';
            echo '<div class="numbertext">' . ($index + 1) . ' / ' . count($files) . '</div>';
            echo '</div>';
        }
    }
    
    return $images;
}

$newsItems = getNewsItems();        
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
    <link href="favicon.ico" rel="shortcut icon" type="image/x-icon">
    <script src="scripts/ticker.js"></script>
    <script src="scripts/slideshow.js"></script>
    <script src="scripts/finance.js"></script>
    <script src="scripts/weather.js"></script>
    <title>The Game Over Bar</title>
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
        <?php
        $financeItems = ['usd', 'eur', 'btc', 'aapl', 'googl', 'msft', 'tsla', 'meta', 'now', 'nvda'];
        foreach ($financeItems as $item): ?>
            <div class="finance-data" id="<?= $item ?>-data">
                <span class="finance-label">Loading...</span>
                <span class="finance-value"><?= strtoupper($item) ?></span>
            </div>
        <?php endforeach; ?>
        <div class="finance-data" id="datetime">
            <span class="finance-label">Loading...</span>
            <span class="finance-value">Loading...</span>
        </div>
    </div>

    <img src="images/GameOverBar.png" alt="Game Over Bar" class="titlelogo">
    
    <div class="slideshow-container">
        <?php getSlideshowImages(); ?>
        
        <div class="weather-container">
            <div class="current-weather">
                <div class="weather-info">
                    <div class="location">Didcot, UK</div>
                    <div class="description" id="weather-desc">--</div>
                </div>
                <div class="temp-now">
                    <img src="" alt="Current Weather" class="weather-icon" id="current-icon">
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
                        <img src="" alt="Forecast" class="weather-icon small" id="icon-<?=$i?>">
                        <div class="temp" id="temp-<?=$i?>">--°C</div>
                    </div>
                <?php endfor; ?>
            </div>
        </div>
        
        <img src="images/BensonGamesLogo.png" alt="Benson Games Logo" class="logo">
    </div>
</body>
</html>
