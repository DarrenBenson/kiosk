// Fetches and updates bin collection information
async function fetchBinCollection() {
    try {
        const response = await fetch('/api/bins.php');
        const data = await response.json();

        if (data.error) {
            console.error('Bin collection error:', data.error);
            document.getElementById('bin-date').textContent = 'Check schedule';
            return;
        }

        // Update bin date
        const dateText = data.estimated ? `~${data.date}` : data.date;
        document.getElementById('bin-date').textContent = dateText;

        // Update bin icons
        document.getElementById('green-bin').classList.toggle('active', data.bins.green);
        document.getElementById('grey-bin').classList.toggle('active', data.bins.grey);
        document.getElementById('brown-bin').classList.toggle('active', data.bins.brown);
    } catch (error) {
        console.error('Error fetching bin collection data:', error);
        document.getElementById('bin-date').textContent = 'Error loading';
    }
}

// Calculate milliseconds until next midnight
function getMillisecondsUntilMidnight() {
    const now = new Date();
    const tomorrow = new Date(now);
    tomorrow.setDate(tomorrow.getDate() + 1);
    tomorrow.setHours(0, 0, 0, 0);
    return tomorrow - now;
}

// Initialize bin updates
function startBinUpdates() {
    fetchBinCollection(); // Initial fetch

    // Update at midnight each day
    const updateAtMidnight = () => {
        fetchBinCollection();
        setTimeout(updateAtMidnight, getMillisecondsUntilMidnight());
    };

    setTimeout(updateAtMidnight, getMillisecondsUntilMidnight());
}

// Start bin updates when DOM is loaded
document.addEventListener('DOMContentLoaded', startBinUpdates); 