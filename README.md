```
 _  ___           _
| |/ (_) ___  ___| | __
| ' /| |/ _ \/ __| |/ /
| . \| | (_) \__ \   <
|_|\_\_|\___/|___/_|\_\

   Digital Signage Display System
```

[![MIT Licence](https://img.shields.io/badge/licence-MIT-blue.svg)](LICENSE.md)

A configurable kiosk display system showing news, weather, stocks, and more.

## What is This?

Kiosk is a self-hosted digital signage system designed for venues, offices, or home displays. It shows:

- **News ticker** - Scrolling headlines from any RSS feed
- **Weather forecast** - Current conditions and 8-hour forecast
- **Stock prices** - Real-time quotes from US and UK markets
- **Currency rates** - USD, EUR, and BTC exchange rates
- **Bin collection** - UK council waste collection schedule
- **Slideshow** - Auto-cycling images for your venue

All features are optional and auto-disable when their API keys aren't configured.

## Demo

**Live demo:** https://kiosk.deskpoint.com

## Prerequisites

| Requirement | Version | How to check |
|-------------|---------|--------------|
| PHP | 7.4+ | `php --version` |
| Web server | Any | nginx, Apache, or PHP built-in |
| cURL extension | - | `php -m \| grep curl` |

## Installation

### Option 1: Docker (recommended)

```bash
# Download the compose file
curl -O https://raw.githubusercontent.com/DarrenBenson/kiosk/main/compose.yaml

# Edit compose.yaml and add your API keys
nano compose.yaml

# Start the container
docker compose up -d
```

Open http://localhost:8080 in your browser.

The compose file includes all configuration options with descriptions. At minimum, add your `WEATHER_API_KEY` to see the weather widget.

### Option 2: Standalone PHP

```bash
# Clone the repository
git clone https://github.com/DarrenBenson/kiosk.git
cd kiosk

# Create your configuration
cp config.example.php config.local.php

# Edit config.local.php with your API keys
nano config.local.php

# Create cache directory
mkdir -p cache && chmod 755 cache

# Start PHP development server
php -S localhost:8080
```

### Option 3: Build from source

```bash
# Clone and build
git clone https://github.com/DarrenBenson/kiosk.git
cd kiosk
docker build -t kiosk .

# Run with environment variables
docker run -d -p 8080:80 \
  -e WEATHER_API_KEY=your_key_here \
  -e ALPACA_API_KEY=your_key_here \
  -e ALPACA_API_SECRET=your_secret_here \
  kiosk
```

### Option 4: Production deployment

See [SETUP.md](SETUP.md) for nginx/Apache configuration.

## Configuration

Kiosk uses a layered configuration system:

1. `config.local.php` - Your local settings (gitignored)
2. Environment variables - For Docker deployments
3. Default values - Sensible fallbacks

### Getting API Keys

| Service | Purpose | Get key at |
|---------|---------|------------|
| OpenWeatherMap | Weather data | https://openweathermap.org/api |
| Alpaca Markets | US stock prices | https://alpaca.markets/ |
| Alpha Vantage | UK stock prices | https://www.alphavantage.co/ |

### Configuration Options

| Setting | Default | Description |
|---------|---------|-------------|
| `SITE_TITLE` | Kiosk Display | Page title |
| `LOGO_LEFT` | (none) | Path to left logo image |
| `LOGO_RIGHT` | (none) | Path to right logo image |
| `NEWS_RSS_URL` | BBC UK News | RSS feed URL for news ticker |
| `DISPLAY_CURRENCY` | GBP | Currency for display (GBP, USD, EUR) |
| `LOCALE` | en-GB | Date/number formatting locale |
| `WEATHER_API_KEY` | (none) | OpenWeatherMap API key |
| `WEATHER_LAT` | 51.5074 | Latitude (default: London) |
| `WEATHER_LON` | -0.1278 | Longitude (default: London) |
| `WEATHER_LOCATION` | London, UK | Display name for location |
| `ALPACA_API_KEY` | (none) | Alpaca Markets API key |
| `ALPACA_API_SECRET` | (none) | Alpaca Markets API secret |
| `DEFAULT_STOCK_SYMBOLS` | AAPL,GOOGL,... | Comma-separated stock symbols |
| `BINS_ENABLED` | true | Enable bin collection widget |
| `BIN_UPRN` | (none) | Your property's UPRN |
| `BIN_COUNCIL` | SOUTH | Council: SOUTH or VALE |

### Feature Auto-Disable

Features automatically hide when their required configuration is missing:

| Feature | Requires |
|---------|----------|
| Weather widget | `WEATHER_API_KEY` |
| Stock prices | `ALPACA_API_KEY` |
| Bin collection | `BIN_UPRN` |

Currency rates (USD, EUR, BTC) always display as they use free APIs.

## Project Structure

```
kiosk/
├── api/                     # Backend PHP APIs
│   ├── bins.php             # Bin collection data
│   ├── stocks.php           # Stock quotes
│   └── weather.php          # Weather forecast
├── scripts/                 # JavaScript modules
│   ├── slideshow.js         # Image carousel
│   ├── ticker.js            # News ticker
│   ├── weather.js           # Weather widget
│   └── finance.js           # Stocks and currency
├── style/                   # CSS files
├── content/4x3/             # Slideshow images (add yours here)
├── images/                  # UI assets
├── cache/                   # API response cache (gitignored)
├── config.php               # Main config loader
├── config.local.php         # Your settings (gitignored)
├── config.example.php       # Template for config.local.php
└── index.php                # Main application
```

## Customisation

### Adding slideshow images

Add images to `content/4x3/`. Supported formats: JPG, PNG, WebP, GIF.

```bash
cp your-image.jpg content/4x3/slide01.jpg
```

Images are displayed in alphabetical order.

### Changing the news feed

Edit `NEWS_RSS_URL` in your config:

```php
define('NEWS_RSS_URL', 'https://feeds.bbci.co.uk/news/technology/rss.xml');
```

### Adjusting refresh intervals

Edit the JavaScript files in `scripts/`:

```javascript
// Weather: refresh every 5 minutes
setInterval(() => Weather.fetch(), 300000);

// Slideshow: change every 10 seconds
this.interval = 10000;
```

## Bin Collection (UK Only)

The bin collection widget works with South Oxfordshire and Vale of White Horse councils via the Binzone service.

### Finding your UPRN

1. Visit https://www.findmyaddress.co.uk/
2. Enter your postcode
3. Copy the 11-12 digit UPRN

### Setting up bin cache (recommended)

For reliable bin data, set up a daily cron job:

```bash
# Add to crontab
0 6 * * * cd /path/to/kiosk && python3 api/update_bins_cache.py
```

This fetches bin data once daily rather than on every page load.

## Troubleshooting

| Issue | Solution |
|-------|----------|
| Blank page | Check PHP error log, ensure config.local.php exists |
| No weather | Verify `WEATHER_API_KEY` is set correctly |
| No stocks | Check `ALPACA_API_KEY` and `ALPACA_API_SECRET` |
| No bins | Verify `BIN_UPRN` and check cache directory permissions |
| No images | Add images to `content/4x3/` directory |
| Cache errors | Run `chmod 755 cache` to fix permissions |

### Checking API endpoints

Test the APIs directly:

```bash
curl http://localhost:8080/api/weather.php
curl http://localhost:8080/api/stocks.php
curl http://localhost:8080/api/bins.php
```

## Development

### Running PHPStan

```bash
docker run --rm -v $(pwd):/app ghcr.io/phpstan/phpstan analyse -c phpstan.neon
```

### Code style

- PHP: PSR-12 with strict types
- JavaScript: Object literal pattern with `init()` methods
- CSS: BEM-ish naming, `clamp()` for responsive sizing

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md) for guidelines.

## Security

See [SECURITY.md](SECURITY.md) for vulnerability reporting.

## Licence

MIT Licence - see [LICENSE.md](LICENSE.md) for details.
