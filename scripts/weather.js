/**
 * Weather Display Module
 * Fetches and displays weather data from OpenWeatherMap via backend API
 */
const Weather = {
    /**
     * Fetches weather data from the API
     */
    async fetch() {
        try {
            const response = await fetch('/api/weather.php');
            const data = await response.json();

            if (data.current?.error) {
                console.warn('Weather warning:', data.current.error);
                return;
            }

            this.update(data);
        } catch (error) {
            console.error('Error fetching weather data:', error);
        }
    },

    /**
     * Updates the DOM with weather data
     * @param {Object} data - Weather data from API
     */
    update(data) {
        const current = data.current;

        if (!current || !current.main) {
            return;
        }

        // Update current temperature
        const tempEl = document.getElementById('current-temp');
        if (tempEl) {
            tempEl.textContent = `${Math.round(current.main.temp)}°C`;
        }

        // Update feels like
        const feelsLikeEl = document.getElementById('feels-like-temp');
        if (feelsLikeEl) {
            feelsLikeEl.textContent = `${Math.round(current.main.feels_like)}°C`;
        }

        // Update description
        const descEl = document.getElementById('weather-desc');
        if (descEl && current.weather?.[0]) {
            const desc = current.weather[0].description;
            descEl.textContent = desc.charAt(0).toUpperCase() + desc.slice(1);
        }

        // Update weather icon
        const iconEl = document.getElementById('current-icon');
        if (iconEl && current.weather?.[0]) {
            iconEl.src = `https://openweathermap.org/img/wn/${current.weather[0].icon}@2x.png`;
        }

        // Update sunrise/sunset times
        if (current.sys) {
            const sunriseEl = document.getElementById('sunrise-time');
            const sunsetEl = document.getElementById('sunset-time');

            if (sunriseEl && current.sys.sunrise) {
                const sunrise = new Date(current.sys.sunrise * 1000);
                sunriseEl.textContent = sunrise.toLocaleTimeString('en-GB', {
                    hour: '2-digit',
                    minute: '2-digit'
                });
            }

            if (sunsetEl && current.sys.sunset) {
                const sunset = new Date(current.sys.sunset * 1000);
                sunsetEl.textContent = sunset.toLocaleTimeString('en-GB', {
                    hour: '2-digit',
                    minute: '2-digit'
                });
            }
        }

        // Update hourly forecast
        if (data.hourly) {
            data.hourly.forEach((hour, index) => {
                const timeEl = document.getElementById(`hour-${index + 1}`);
                const tempEl = document.getElementById(`temp-${index + 1}`);
                const iconEl = document.getElementById(`icon-${index + 1}`);

                if (timeEl) {
                    const time = new Date(hour.dt * 1000);
                    timeEl.textContent = time.toLocaleTimeString('en-GB', {
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                }

                if (tempEl && hour.main) {
                    tempEl.textContent = `${Math.round(hour.main.temp)}°C`;
                }

                if (iconEl && hour.weather?.[0]) {
                    iconEl.src = `https://openweathermap.org/img/wn/${hour.weather[0].icon}.png`;
                }
            });
        }
    },

    /**
     * Initializes weather updates
     */
    init() {
        this.fetch();
        // Update every 5 minutes
        setInterval(() => this.fetch(), 300000);
    }
};

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => Weather.init());
