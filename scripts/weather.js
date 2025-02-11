// Fetches and updates weather data for Didcot
async function fetchWeatherData() {
    try {
        const response = await fetch('/api/weather.php');
        const data = await response.json();
        
        if (data.error) {
            throw new Error(data.error);
        }
        
        // Update current weather
        const current = data.current;
        document.getElementById('current-temp').textContent = 
            `${Math.round(current.main.temp)}°C`;
        document.getElementById('feels-like-temp').textContent = 
            `${Math.round(current.main.feels_like)}°C`;
        document.getElementById('weather-desc').textContent = 
            current.weather[0].description.charAt(0).toUpperCase() + 
            current.weather[0].description.slice(1);
        document.getElementById('current-icon').src = 
            `https://openweathermap.org/img/wn/${current.weather[0].icon}@2x.png`;
        
        // Update sunrise/sunset times
        const sunrise = new Date(current.sys.sunrise * 1000);
        const sunset = new Date(current.sys.sunset * 1000);
        document.getElementById('sunrise-time').textContent = 
            sunrise.toLocaleTimeString('en-GB', {hour: '2-digit', minute: '2-digit'});
        document.getElementById('sunset-time').textContent = 
            sunset.toLocaleTimeString('en-GB', {hour: '2-digit', minute: '2-digit'});
        
        // Update hourly forecast
        data.hourly.forEach((hour, index) => {
            const time = new Date(hour.dt * 1000);
            document.getElementById(`hour-${index + 1}`).textContent = 
                time.toLocaleTimeString('en-GB', {hour: '2-digit', minute: '2-digit'});
            document.getElementById(`temp-${index + 1}`).textContent = 
                `${Math.round(hour.main.temp)}°C`;
            document.getElementById(`icon-${index + 1}`).src = 
                `https://openweathermap.org/img/wn/${hour.weather[0].icon}.png`;
        });
    } catch (error) {
        console.error('Error fetching weather data:', error);
    }
}

// Initialize weather updates
function startWeatherUpdates() {
    fetchWeatherData(); // Initial fetch
    setInterval(fetchWeatherData, 300000); // Update every 5 minutes
}

// Start weather updates when DOM is loaded
document.addEventListener('DOMContentLoaded', startWeatherUpdates); 