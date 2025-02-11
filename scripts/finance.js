// Fetches and updates currency exchange rates for USD, EUR, and BTC against GBP
// Uses CoinGecko API for BTC and ExchangeRate API for fiat currencies
async function fetchCurrencyRates() {
    try {
        // Fetch rates from CoinGecko API for BTC
        const btcResponse = await fetch('https://api.coingecko.com/api/v3/simple/price?ids=bitcoin&vs_currencies=gbp');
        const btcData = await btcResponse.json();
        const btcRate = btcData.bitcoin.gbp.toLocaleString('en-GB', {
            maximumFractionDigits: 0,
            minimumFractionDigits: 0
        });

        // Fetch rates from ExchangeRate API for USD and EUR
        const response = await fetch('https://api.exchangerate-api.com/v4/latest/GBP');
        const data = await response.json();
        
        // Update the DOM with new rates
        document.querySelector('#usd-rate span').textContent = "£" + (1 / data.rates.USD).toFixed(2);
        document.querySelector('#eur-rate span').textContent = "£" + (1 / data.rates.EUR).toFixed(2);
        document.querySelector('#btc-rate span').textContent = "£" + btcRate;
    } catch (error) {
        console.error('Error fetching currency rates:', error);
    }
}

// Updates the date and time display
function updateDateTime() {
    const now = new Date();
    const dateOptions = { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' };
    const timeOptions = { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false };
    
    document.querySelector('#datetime .date').textContent = now.toLocaleDateString('en-GB', dateOptions);
    document.querySelector('#datetime .time').textContent = now.toLocaleTimeString('en-GB', timeOptions);
}

// Initializes currency updates and refreshes them every minute
function startCurrencyUpdates() {
    fetchCurrencyRates(); // Initial fetch
    setInterval(fetchCurrencyRates, 60000); // Update every minute
}

// Initializes date and time updates and refreshes them every second
function startDateTimeUpdates() {
    updateDateTime(); // Initial update
    setInterval(updateDateTime, 1000); // Update time every second
}

// Initialize all updates when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    startCurrencyUpdates();
    startDateTimeUpdates();
}); 