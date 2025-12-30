# CLAUDE.md

Guidance for Claude Code when working with this repository.

## Important: Always Use Context7

**Before writing or modifying code, always check documentation with context7 MCP server.**

```
Use context7 to look up:
- PHP best practices and syntax
- JavaScript patterns
- CSS techniques
- Any library or API being used
```

This ensures code follows current best practices and correct syntax.

## Project Overview

PHP/JavaScript kiosk display for Game Over Bar - digital signage showing news, weather, stocks, bin collection, and slideshow.

**Live URL:** https://kiosk.deskpoint.com

## Commands

Run `/kiosk-help` for full command list.

| Command | Description |
|---------|-------------|
| `/deploy` | Deploy files to production |
| `/test` | Deploy to test GUID |
| `/check` | Verify APIs are working |
| `/kiosk-help` | Show all commands |

## Project Structure

```
kiosk/
├── .claude/
│   ├── commands/        # Slash commands
│   └── skills/
│       └── deploy-skill/scripts/deploy.sh
├── api/                 # Backend PHP APIs
│   ├── bins.php         # South Oxon council bin collection
│   ├── stocks.php       # Alpaca Markets stock quotes
│   └── weather.php      # OpenWeatherMap forecast
├── scripts/             # JavaScript modules
├── style/               # CSS files
├── content/4x3/         # Slideshow images
├── cache/               # API response cache (git-ignored)
├── config.php           # Configuration (git-ignored)
└── index.php            # Main application
```

## Deployment

```bash
.claude/skills/deploy-skill/scripts/deploy.sh api/bins.php     # Single file
.claude/skills/deploy-skill/scripts/deploy.sh -g -a            # Test with GUID
.claude/skills/deploy-skill/scripts/deploy.sh -b -a            # Backup + production
```

**Server:** webserver1 via jumpbox
**Path:** `/DockerData/docker/WebSites/kiosk/`
**Backups:** `/DockerData/docker/WebSites/backups/`

## Configuration

`config.php` contains:
- **Weather:** OpenWeatherMap API key, Didcot coordinates
- **Stocks:** Alpaca Markets API credentials
- **Bin Collection:** S2 Friday schedule, Google Calendar ID
- **Cache durations:** bins (24h), weather (5m), exchange rates (1h)

## Architecture

### JavaScript Modules (scripts/)
- **Slideshow** (class) - Image cycling
- **NewsTicker** (class) - Scrolling news
- **Weather** (object) - 5-minute refresh
- **Finance** (object) - Stocks and currency rates
- **BinCollection** (object) - Hourly refresh

### APIs (api/)
All endpoints require `KIOSK_APP` constant, return JSON.

## Code Standards

- **PHP:** Use `require_once __DIR__ . '/../config.php'` for config
- **JavaScript:** Object literal or class pattern with `init()` method
- **CSS:** Use `clamp()` for responsive sizing
- No build step - vanilla JS only
- Two-hop SSH deployment via jumpbox

## MCP Servers

Available via `.mcp.json`:
- **context7** - Documentation lookup (always use before coding)
- **memory** - Project-specific memory storage
- **sequential-thinking** - Complex reasoning tasks

## Best Practices

When creating new files, follow patterns from:
- `/home/darren/code/Engram-Labs-UK/engram-framework/.claude/best-practices/`

Key points:
- British English (analyse, colour, behaviour)
- No emojis unless requested
- Dense, economical writing
- Scannable formatting (headers, bullets, tables)
