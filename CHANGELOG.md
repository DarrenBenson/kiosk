# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

## [Unreleased]

### Added
- PHP strict types and best practices compliance
- PHPStan static analysis configuration
- Configurable branding (logos, site title)
- Auto-disable features when API keys are missing
- GitHub community files (CODE_OF_CONDUCT, SUPPORT, CHANGELOG)
- GitHub issue and PR templates

### Changed
- Made all configuration options generic and customisable
- Improved input validation and sanitisation in API endpoints

## [1.0.0] - 2024-12-30

### Added
- Major UI improvements and stock ticker enhancements
- Secure API key management via environment variables
- Bin collection status display for South Oxfordshire/Vale councils
- Weather forecast display with OpenWeatherMap integration
- Stock price display with Alpaca Markets integration
- Currency exchange rates (USD, EUR, BTC)
- Responsive layout for various screen sizes
- Real-time news ticker with BBC RSS feed
- Image slideshow for venue content

### Security
- Removed hardcoded API keys
- Added config.local.php to gitignore
- Input sanitisation on all API endpoints

## [0.1.0] - Initial Release

### Added
- Basic kiosk display with news ticker
- Slideshow functionality
- Initial project structure
