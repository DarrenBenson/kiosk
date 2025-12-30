# Kiosk Display

Digital signage system for Game Over Bar showing news, weather, stocks, bin collection, and slideshow.

## Features

- BBC news ticker with scrolling headlines
- Weather forecast with 8-hour hourly display
- Stock prices and currency rates (Alpaca Markets)
- Bin collection schedule (South Oxfordshire Council)
- Auto-cycling image slideshow

## Quick Start

```bash
# 1. Copy config template
cp config.example.php config.php

# 2. Add your API keys to config.php
# - OpenWeatherMap API key
# - Alpaca Markets credentials
# - Bin calendar ID from South Oxon Council

# 3. Ensure cache directory exists
mkdir -p cache

# 4. Point web server to project root
```

## Structure

```
kiosk/
├── .claude/
│   ├── commands/        # Slash commands
│   └── skills/          # Skills with scripts
├── api/                 # Backend APIs
│   ├── bins.php         # Bin collection data
│   ├── stocks.php       # Stock quotes
│   └── weather.php      # Weather forecast
├── scripts/             # JavaScript modules
├── style/               # CSS files
├── content/4x3/         # Slideshow images
├── images/              # UI assets
├── cache/               # API cache (git-ignored)
├── config.php           # Configuration (git-ignored)
└── index.php            # Main application
```

## Deployment

```bash
# Test deployment
.claude/skills/deploy-skill/scripts/deploy.sh -g -a

# Production with backup
.claude/skills/deploy-skill/scripts/deploy.sh -b -a
```

**Server:** webserver1 via jumpbox
**URL:** https://kiosk.deskpoint.com

## Configuration

Edit `config.php`:

| Setting | Description |
|---------|-------------|
| `WEATHER_API_KEY` | OpenWeatherMap API key |
| `WEATHER_LAT/LON` | Location coordinates |
| `ALPACA_API_KEY` | Alpaca Markets key |
| `ALPACA_API_SECRET` | Alpaca Markets secret |
| `BIN_CALENDAR_TYPE` | S1 or S2 schedule |
| `BIN_COLLECTION_DAY` | Collection day |
| `BIN_CALENDAR_ID_*` | Google Calendar ID |

## Requirements

- PHP 7.4+
- Web server (nginx/Apache)
- Internet connection for external APIs

## Customisation

### Slideshow timing

```javascript
// scripts/slideshow.js
this.interval = 10000; // milliseconds between slides
```

### Refresh intervals

```javascript
// scripts/weather.js
setInterval(() => this.fetch(), 300000);  // 5 minutes

// scripts/bins.js
setInterval(() => this.fetch(), 3600000); // 1 hour
```

## Troubleshooting

| Issue | Solution |
|-------|----------|
| No images | Add images to `content/4x3/` |
| Weather empty | Check `WEATHER_API_KEY` in config |
| Bins not loading | Verify calendar ID and schedule type |
| Cache errors | Ensure `cache/` is writable (chmod 777) |

## Licence

MIT - see [LICENSE.md](LICENSE.md)
