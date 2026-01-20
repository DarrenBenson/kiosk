# Support

## Getting Help

### Documentation

- [README](README.md) - Project overview and quick start
- [SETUP](SETUP.md) - Detailed configuration guide

### Issue Tracker

For bugs and feature requests, please use the [GitHub Issues](https://github.com/DarrenBenson/kiosk/issues).

Before creating an issue:

1. Search existing issues to avoid duplicates
2. Use the appropriate issue template
3. Provide clear reproduction steps for bugs

## Frequently Asked Questions

### How do I configure the weather widget?

Get a free API key from [OpenWeatherMap](https://openweathermap.org/api) and set `WEATHER_API_KEY` in your config. See SETUP.md for details.

### How do I add my own stock symbols?

Set `DEFAULT_STOCK_SYMBOLS` to a comma-separated list of ticker symbols (e.g., `AAPL,GOOGL,MSFT`). You'll need an [Alpaca Markets](https://alpaca.markets/) API key.

### Can I use this outside the UK?

Yes, but the bin collection feature only works with South Oxfordshire / Vale of White Horse councils. Set `BINS_ENABLED=false` to disable it.

### How do I change the slideshow images?

Add your images to the `content/4x3/` directory. The slideshow automatically cycles through all images in that folder.

## Contributing

Interested in contributing? See our [Contributing Guide](CONTRIBUTING.md).
