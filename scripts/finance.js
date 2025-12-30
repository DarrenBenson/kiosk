/**
 * Finance Display Module
 * Handles currency rates, stock prices, and date/time display
 */
const Finance = {
    previousRates: {},

    /**
     * Gets direction arrow for currency
     * @param {string} key - Currency key
     * @param {number} currentValue - Current value
     * @param {number|null} change24h - 24h change (for BTC)
     * @returns {object} Arrow and direction class
     */
    getCurrencyDirection(key, currentValue, change24h = null) {
        // For BTC, use 24h change from API
        if (change24h !== null) {
            if (change24h > 0) return { arrow: ' ↑', className: 'price-up' };
            if (change24h < 0) return { arrow: ' ↓', className: 'price-down' };
            return { arrow: '', className: '' };
        }

        // For fiat currencies, compare to previous fetch
        const prev = this.previousRates[key];
        if (prev === undefined) return { arrow: '', className: '' };
        if (currentValue > prev) return { arrow: ' ↑', className: 'price-up' };
        if (currentValue < prev) return { arrow: ' ↓', className: 'price-down' };
        return { arrow: '', className: '' };
    },

    /**
     * Fetches currency rates (USD, EUR against GBP and BTC price)
     */
    async fetchCurrencyRates() {
        try {
            // Fetch BTC from CoinGecko with 24h change
            const btcResponse = await fetch('https://api.coingecko.com/api/v3/simple/price?ids=bitcoin&vs_currencies=gbp&include_24hr_change=true');
            const btcData = await btcResponse.json();

            // Fetch fiat rates from ExchangeRate API
            const response = await fetch('https://api.exchangerate-api.com/v4/latest/GBP');
            const data = await response.json();

            const usdRate = parseFloat((1 / data.rates.USD).toFixed(4));
            const eurRate = parseFloat((1 / data.rates.EUR).toFixed(4));
            const btcPrice = btcData.bitcoin.gbp;
            const btc24hChange = btcData.bitcoin.gbp_24h_change;

            this.updateCurrencyDisplay({
                usd: { value: usdRate, display: usdRate.toFixed(2) },
                eur: { value: eurRate, display: eurRate.toFixed(2) },
                btc: {
                    value: btcPrice,
                    display: btcPrice.toLocaleString('en-GB', { minimumFractionDigits: 0, maximumFractionDigits: 0 }),
                    change24h: btc24hChange
                }
            });

            // Store for next comparison
            this.previousRates.usd = usdRate;
            this.previousRates.eur = eurRate;
        } catch (error) {
            console.error('Error fetching currency rates:', error);
        }
    },

    /**
     * Updates currency display in DOM
     * @param {Object} rates - Currency rates with value and display
     */
    updateCurrencyDisplay(rates) {
        Object.entries(rates).forEach(([key, data]) => {
            const el = document.querySelector(`#${key}-data .finance-label`);
            if (el) {
                const direction = this.getCurrencyDirection(key, data.value, data.change24h || null);
                el.textContent = `£${data.display}${direction.arrow}`;

                el.classList.remove('price-up', 'price-down');
                if (direction.className) el.classList.add(direction.className);
            }
        });
    },

    /**
     * Gets direction arrow based on price vs previous close
     * @param {number} currentPrice - Current price
     * @param {number|null} prevClose - Previous day's close
     * @returns {string} Arrow character or empty string
     */
    getDirectionArrow(currentPrice, prevClose) {
        if (prevClose === null || prevClose === undefined) return '';
        if (currentPrice > prevClose) return ' ↑';
        if (currentPrice < prevClose) return ' ↓';
        return '';
    },

    /**
     * Fetches stock prices from backend API
     */
    async fetchStockPrices() {
        try {
            const symbols = 'AAPL,GOOGL,MSFT,AMZN,META,AMD,NVDA';
            const response = await fetch(`/api/stocks.php?symbols=${encodeURIComponent(symbols)}&currency=GBP`);
            const data = await response.json();

            if (data.error) {
                console.warn('Stock prices warning:', data.error);
                return;
            }

            Object.entries(data).forEach(([symbol, stockData]) => {
                const el = document.querySelector(`#${symbol.toLowerCase()}-data .finance-label`);
                if (el) {
                    const price = stockData.price;
                    const prevClose = stockData.prevClose;
                    const arrow = this.getDirectionArrow(price, prevClose);
                    el.textContent = `£${price.toFixed(2)}${arrow}`;

                    // Update colour based on direction vs previous close
                    el.classList.remove('price-up', 'price-down');
                    if (arrow === ' ↑') el.classList.add('price-up');
                    if (arrow === ' ↓') el.classList.add('price-down');
                }
            });
        } catch (error) {
            console.error('Error fetching stock prices:', error);
        }
    },

    /**
     * Updates date and time display
     */
    updateDateTime() {
        const now = new Date();
        const dateOptions = { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' };
        const timeOptions = { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false };

        const dateEl = document.querySelector('#datetime .finance-value');
        const timeEl = document.querySelector('#datetime .finance-label');

        if (dateEl) {
            dateEl.textContent = now.toLocaleDateString('en-GB', dateOptions);
        }
        if (timeEl) {
            timeEl.textContent = now.toLocaleTimeString('en-GB', timeOptions);
        }
    },

    /**
     * Initializes all finance updates
     */
    init() {
        this.fetchCurrencyRates();
        this.fetchStockPrices();
        this.updateDateTime();

        // Refresh currency and stocks every 5 minutes
        setInterval(() => {
            this.fetchCurrencyRates();
            this.fetchStockPrices();
        }, 300000);

        // Update time every second
        setInterval(() => this.updateDateTime(), 1000);
    }
};

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => Finance.init());
