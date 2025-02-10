<?php
// Load news items from BBC RSS feed
function getNewsItems() {
    $items = []; // Initialize an array to hold news items
    $xml = @simplexml_load_file("http://feeds.bbci.co.uk/news/uk/rss.xml"); // Load the RSS feed
    

    // Check if the RSS feed was loaded successfully
    if ($xml === false) {
        error_log("Failed to load BBC RSS feed"); // Log an error if loading fails
        return $items; // Return an empty array
    }
    
    // Iterate through each item in the RSS feed
    foreach ($xml->channel->item as $item) {
        $items[] = [
            'title' => htmlspecialchars((string)$item->title, ENT_QUOTES, 'UTF-8'), // Sanitize title
            'description' => htmlspecialchars((string)$item->description, ENT_QUOTES, 'UTF-8'), // Sanitize description
            'link' => htmlspecialchars((string)$item->link, ENT_QUOTES, 'UTF-8'), // Sanitize link
            'pubDate' => htmlspecialchars((string)$item->pubDate, ENT_QUOTES, 'UTF-8') // Sanitize publication date
        ];
    }
    
    return $items; // Return the array of news items
}

// Load slideshow images from a specific directory
function getSlideshowImages() {
    $images = []; // Initialize an array to hold slideshow images
    $files = glob("content/4x3/*"); // Get all image files in the specified directory
    
    // Check if any files were found
    if (!empty($files)) {
        // Iterate through each file and prepare image data
        foreach ($files as $index => $file) {
            $images[] = [
                'path' => htmlspecialchars($file, ENT_QUOTES, 'UTF-8'), // Sanitize file path
                'number' => $index + 1, // Image number in the slideshow
                'total' => count($files) // Total number of images
            ];
        }
    }
    
    return $images; // Return the array of slideshow images
}

// Fetch news items and slideshow images
$newsItems = getNewsItems(); // Call function to get news items
$slideshowImages = getSlideshowImages(); // Call function to get slideshow images
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="style/slideshow.css"> <!-- Link to slideshow CSS -->
    <link rel="stylesheet" type="text/css" href="style/ticker.css"> <!-- Link to ticker CSS -->
    <link href="favicon.ico" rel="shortcut icon" type="image/x-icon"> <!-- Favicon -->
    <script src="scripts/ticker.js"></script> <!-- Link to ticker JavaScript -->
    <script src="scripts/slideshow.js"></script> <!-- Link to slideshow JavaScript -->
    <title>The Game Over Bar - News and Events</title> <!-- Page title -->
</head>
<body>
    <div class="ticker-container">
        <div class="ticker-caption"><p>Breaking News</p></div>
        <ul>
            <?php foreach ($newsItems as $item): ?> <!-- Loop through news items -->
                <div>
                    <li>
                        <span>
                            <strong><?= $item['title'] ?></strong> - <?= $item['description'] ?> <!-- Display title and description -->
                        </span>
                    </li>
                </div>
            <?php endforeach; ?>
        </ul>
    </div>

    <img src="images/GameOverBar.png" alt="Game Over Bar" class="titlelogo"> <!-- Logo image -->
    
    <div class="slideshow-container">
        <?php foreach ($slideshowImages as $image): ?> <!-- Loop through slideshow images -->
            <div class="mySlides fade">
                <div class="numbertext"><?= $image['number'] ?> / <?= $image['total'] ?></div> <!-- Display image number -->
                <img src="<?= $image['path'] ?>" style="width:1024" alt="Slideshow Image"> <!-- Slideshow image -->
            </div>
        <?php endforeach; ?>
        
        <img src="images/BensonGamesLogo.png" alt="Benson Games Logo" class="logo"> <!-- Benson Games logo -->
    </div>
</body>
</html>
