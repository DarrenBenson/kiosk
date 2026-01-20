# Kiosk Display Setup Guide

This guide will help you configure your kiosk display with all the necessary API keys and settings.

## Quick Start

1. Copy the example config file:
   ```bash
   cp config.example.php config.php
   ```

2. Edit `config.php` and add your API keys and settings
3. Deploy to your server or restart your Docker container

## API Keys Setup

### 1. OpenWeatherMap API Key

**Purpose:** Displays current weather and hourly forecast for Didcot

**How to get it:**
1. Visit [https://openweathermap.org/api](https://openweathermap.org/api)
2. Click "Sign Up" and create a free account
3. Go to "API keys" section
4. Copy your API key
5. Add to `config.php`: `define('WEATHER_API_KEY', 'your_key_here');`

**Free tier limits:** 1,000 calls/day (more than enough for the kiosk)

### 2. Alpaca API Keys

**Purpose:** Displays stock prices (AAPL, GOOGL, MSFT, TSLA, META, NOW, NVDA) in GBP

**How to get it:**
1. Visit [https://alpaca.markets/](https://alpaca.markets/)
2. Click "Sign Up" and create a free account
3. Choose "Paper Trading" (not live trading)
4. Go to your dashboard and generate API keys
5. Copy both the API Key and API Secret
6. Add to `config.php`:
   ```php
   define('ALPACA_API_KEY', 'your_key_here');
   define('ALPACA_API_SECRET', 'your_secret_here');
   ```

**Free tier:** Unlimited API calls for market data

### 3. Bin Collection UPRN

**Purpose:** Shows accurate bin collection dates for your address

**Supported councils:** South Oxfordshire and Vale of White Horse District Councils

**How to find your UPRN:**

**Option 1: FindMyAddress.co.uk** (Recommended)
1. Visit [https://www.findmyaddress.co.uk/search](https://www.findmyaddress.co.uk/search)
2. Enter your full address
3. Look for the UPRN (Unique Property Reference Number) - an 11-12 digit number
4. Add to `config.php`:
   ```php
   define('BIN_UPRN', 'your_uprn_here');
   define('BIN_COUNCIL', 'SOUTH');  // or 'VALE' for Vale of White Horse
   ```

**Option 2: Council's Binzone Service**
1. Visit your council's Binzone page
2. Enter your postcode and select your address
3. Open browser DevTools (F12 → Network tab)
4. Look for requests containing your UPRN

**Option 3: Contact the Council**
- Email: waste.team@southandvale.gov.uk
- Phone: 01235 422 422

**Note:** The bin collection feature requires Python 3 with the `requests` library installed on the server.

## Update Frequencies

Once configured, the display will automatically update:

- **Weather:** Every 5 minutes
- **Currency rates (USD, EUR, BTC):** Every 5 minutes
- **Stock prices:** Every 5 minutes
- **Bin collection:** Once per day at midnight
- **News ticker:** On page load (static during session)
- **Date/time:** Every second

## Verifying Setup

After configuring `config.php`:

1. Deploy to your server or restart Docker:
   ```bash
   docker restart kiosk
   ```

2. Open your kiosk URL in a browser

3. Open browser console (F12) to check for any errors

4. Verify that:
   - Temperature shows actual values (not --)
   - Stock prices show GBP values
   - Bin date shows next collection date

## Troubleshooting

### Weather not loading
- Check that `WEATHER_API_KEY` is correctly set in `config.php`
- Verify the API key is active (may take a few minutes after creation)
- Check for errors in browser console

### Stock prices showing "Loading..."
- Check that both `ALPACA_API_KEY` and `ALPACA_API_SECRET` are set
- Verify you created "Paper Trading" keys, not live trading
- Markets may be closed (US market hours: 2:30 PM - 9:00 PM GMT)

### Bin collection not showing
- Verify `BIN_UPRN` is set correctly in `config.php`
- Ensure Python 3 and `requests` library are installed
- Check that `BIN_COUNCIL` matches your council (SOUTH or VALE)

## Security Notes

- **Never commit `config.php` to Git** - it's already in `.gitignore`
- API keys are server-side only and not exposed to the browser
- Use "Paper Trading" keys for Alpaca, never live trading keys
- All API calls are cached appropriately to minimise requests

## 4x3 Display Optimization

The kiosk is optimized for your old-school 4:3 Dell LCD monitor. The responsive layout adapts to the resolution while maintaining readability for a barcade aesthetic.

## Support

For issues or questions:
- Check [OpenWeatherMap Status](https://status.openweathermap.org/)
- Check [Alpaca Status](https://status.alpaca.markets/)
- Review browser console for error messages
- Ensure Docker container has outbound internet access
