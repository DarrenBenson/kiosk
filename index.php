<?php
/**
 * Fetches and sanitizes BBC news feed items with fallback content
 * @return array Array of news items with title, description, link, and finance-value
 */
function getNewsItems() {
    $items = [];
    // Using @ to suppress warnings as we handle the failure case
    $xml = @simplexml_load_file("https://feeds.bbci.co.uk/news/uk/rss.xml");
    
    if ($xml === false) {
        error_log("Failed to load BBC RSS feed");
        // Fallback content shown when feed is unavailable
        return [
            [
                'title' => 'Welcome to Game Over Bar',
                'description' => 'Check back later for the latest news and updates.',
                'link' => '#',
                'pubDate' => finance-value('D, d M Y H:i:s O')
            ],
            [
                'title' => 'Technical Difficulties',
                'description' => 'We are currently unable to load the latest news. Please try again later.',
                'link' => '#',
                'pubDate' => finance-value('D, d M Y H:i:s O')
            ]
        ];
    }
    
    foreach ($xml->channel->item as $item) {
        // Sanitize all feed content to prevent XSS
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
    // Expects images to be in the content/4x3 directory
    $files = glob("content/4x3/*");
    
    if (!empty($files)) {
        foreach ($files as $index => $file) {
            $images[] = [
                'path' => htmlspecialchars($file, ENT_QUOTES, 'UTF-8'),
                'number' => $index + 1,
                'total' => count($files)
            ];
        }
    }
    
    return $images;
}

// Initialize content before rendering
$newsItems = getNewsItems();
$slideshowImages = getSlideshowImages();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="style/slideshow.css">
    <link rel="stylesheet" type="text/css" href="style/ticker.css">
    <link rel="stylesheet" type="text/css" href="style/finance.css">
    <link href="favicon.ico" rel="shortcut icon" type="image/x-icon">
    <script src="scripts/ticker.js"></script>
    <script src="scripts/slideshow.js"></script>
    <script src="scripts/finance.js"></script>
    <title>The Game Over Bar</title>
</head>
<body style="width:1024px">
    <div class="ticker-container">
        <div class="ticker-caption"><p>Breaking News</p></div>
        <ul>
            <?php foreach ($newsItems as $item): ?>
                <div><li><span><strong><?= $item['title'] ?></strong> - <?= $item['description'] ?></span></li></div>
            <?php endforeach; ?>
        </ul>
    </div>

    <div class="finance-container">
        <div class="finance-data" id="usd-rate">
            <span class="finance-label">Loading...</span>
            <span class="finance-value">USD</span>
        </div>
        <div class="finance-data" id="eur-rate">            
            <span class="finance-label">Loading...</span>
            <span class="finance-value">EUR</span>
        </div>
        <div class="finance-data" id="btc-rate">
            <span class="finance-label">Loading...</span>
            <span class="finance-value">BTC</span>
        </div>
        <div class="finance-data" id="aapl-price">
            <span class="finance-label">Loading...</span>
            <span class="finance-value">AAPL</span>
        </div>
        <div class="finance-data" id="googl-price">
            <span class="finance-label">Loading...</span>
            <span class="finance-value">GOOGL</span>
        </div>
        <div class="finance-data" id="msft-price">
            <span class="finance-label">Loading...</span>
            <span class="finance-value">MSFT</span>
        </div>
        <div class="finance-data" id="tsla-price">
            <span class="finance-label">Loading...</span>
            <span class="finance-value">TSLA</span>
        </div>
        <div class="finance-data" id="meta-price">
            <span class="finance-label">Loading...</span>
            <span class="finance-value">META</span>
        </div>
        <div class="finance-data" id="now-price">
            <span class="finance-label">Loading...</span>
            <span class="finance-value">NOW</span>
        </div>
        <div class="finance-data" id="nvda-price">
            <span class="finance-label">Loading...</span>
            <span class="finance-value">NVDA</span>
        </div>
        <div class="finance-data" id="datetime">
            <span class="finance-label">Loading...</span>
            <span class="finance-value">Loading...</span>
        </div>
    </div>

    <img src="images/GameOverBar.png" alt="Game Over Bar" class="titlelogo1024">
    
    <div class="slideshow-container">
        <?php foreach ($slideshowImages as $image): ?>
            <div class="mySlides fade">
                <div class="numbertext"><?= $image['number'] ?> / <?= $image['total'] ?></div>
                <img src="<?= $image['path'] ?>" style="width:1024px" alt="Slideshow Image">
            </div>
        <?php endforeach; ?>
        
        <img src="images/BensonGamesLogo.png" alt="Benson Games Logo" class="logo1024">
    </div>
</body>
</html>
