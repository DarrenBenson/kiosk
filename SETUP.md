# Kiosk Display Setup Guide

This guide will help you configure your kiosk display with all the necessary API keys and settings.

## Quick Start

1. Copy the example environment file:
   ```bash
   cp .env.example .env
   ```

2. Edit `.env` and add your API keys and configuration
3. Restart your Docker container for changes to take effect

## API Keys Setup

### 1. OpenWeatherMap API Key

**Purpose:** Displays current weather and hourly forecast for Didcot

**How to get it:**
1. Visit [https://openweathermap.org/api](https://openweathermap.org/api)
2. Click "Sign Up" and create a free account
3. Go to "API keys" section
4. Copy your API key
5. Add to `.env`: `WEATHER_API_KEY=your_key_here`

**Free tier limits:** 1,000 calls/day (more than enough for the kiosk)

### 2. Alpaca API Keys

**Purpose:** Displays stock prices (AAPL, GOOGL, MSFT, TSLA, META, NOW, NVDA) in GBP

**How to get it:**
1. Visit [https://alpaca.markets/](https://alpaca.markets/)
2. Click "Sign Up" and create a free account
3. Choose "Paper Trading" (not live trading)
4. Go to your dashboard and generate API keys
5. Copy both the API Key and API Secret
6. Add to `.env`:
   ```
   ALPACA_API_KEY=your_key_here
   ALPACA_API_SECRET=your_secret_here
   ```

**Free tier:** Unlimited API calls for market data

### 3. Bin Collection UPRN

**Purpose:** Shows accurate bin collection dates for your address

**Your Address:** 29 Juniper Way, Didcot, OX11 6AA

**How to find your UPRN:**

**Option 1: FindMyAddress.co.uk**
1. Visit [https://www.findmyaddress.co.uk/search](https://www.findmyaddress.co.uk/search)
2. Enter your full address: "29 Juniper Way, Didcot, OX11 6AA"
3. Look for the UPRN (Unique Property Reference Number)
4. Add to `.env`: `BIN_UPRN=your_uprn_here`

**Option 2: Council's Binzone Service**
1. Visit [Vale of White Horse Binzone](https://www.whitehorsedc.gov.uk/vale-of-white-horse-district-council/recycling-rubbish-and-waste/binzone/)
2. Enter your postcode: OX11 6AA
3. Select your address
4. Check the browser's network tab (F12 → Network) to see the UPRN in the request

**Option 3: Contact the Council**
- Email: waste.team@southandvale.gov.uk
- Phone: 01235 422 422
- Ask for the UPRN for 29 Juniper Way, Didcot, OX11 6AA

**Note:** Without the UPRN, the system will estimate bin collection dates based on the typical Vale of White Horse schedule (alternating weeks, Monday collections). This is less accurate than using the UPRN.

## Update Frequencies

Once configured, the display will automatically update:

- **Weather:** Every 5 minutes
- **Currency rates (USD, EUR, BTC):** Every 5 minutes
- **Stock prices:** Every 5 minutes
- **Bin collection:** Once per day at midnight
- **News ticker:** On page load (static during session)
- **Date/time:** Every second

## Verifying Setup

After adding your API keys to `.env`:

1. Restart the Docker container:
   ```bash
   docker restart kiosk
   ```

2. Check the live site: [https://kiosk.deskpoint.com/](https://kiosk.deskpoint.com/)

3. Open browser console (F12) to check for any errors

4. Verify that:
   - Temperature shows actual values (not --)
   - Stock prices show GBP values
   - Bin date shows next collection (may have ~ prefix if UPRN not configured)

## Troubleshooting

### Weather not loading
- Check that `WEATHER_API_KEY` is correctly set in `.env`
- Verify the API key is active (may take a few minutes after creation)
- Check for errors in browser console

### Stock prices showing "Loading..."
- Check that both `ALPACA_API_KEY` and `ALPACA_API_SECRET` are set
- Verify you created "Paper Trading" keys, not live trading
- Markets may be closed (US market hours: 2:30 PM - 9:00 PM GMT)

### Bin collection showing estimated dates
- Add your UPRN to `.env` as `BIN_UPRN=your_uprn_here`
- The estimated schedule assumes Monday collections with alternating weeks

## Security Notes

- **Never commit `.env` to Git** - it's already in `.gitignore`
- API keys are server-side only and not exposed to the browser
- Use "Paper Trading" keys for Alpaca, never live trading keys
- All API calls are cached appropriately to minimize requests

## 4x3 Display Optimization

The kiosk is optimized for your old-school 4:3 Dell LCD monitor. The responsive layout adapts to the resolution while maintaining readability for a barcade aesthetic.

## Support

For issues or questions:
- Check [OpenWeatherMap Status](https://status.openweathermap.org/)
- Check [Alpaca Status](https://status.alpaca.markets/)
- Review browser console for error messages
- Ensure Docker container has outbound internet access
