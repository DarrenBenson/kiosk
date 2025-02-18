// Fetches and updates bin collection information
async function fetchBinCollection() {
    try {
        const response = await fetch('/api/bins.php');
        const data = await response.json();
        
        // Update bin date
        document.getElementById('bin-date').textContent = data.date;
        
        // Update bin icons
        document.getElementById('green-bin').classList.toggle('active', data.bins.green);
        document.getElementById('grey-bin').classList.toggle('active', data.bins.grey);
        document.getElementById('brown-bin').classList.toggle('active', data.bins.brown);
    } catch (error) {
        console.error('Error fetching bin collection data:', error);
    }
}

// Initialize bin updates
function startBinUpdates() {
    fetchBinCollection(); // Initial fetch
    setInterval(fetchBinCollection, 3600000); // Update every hour
}

// Start bin updates when DOM is loaded
document.addEventListener('DOMContentLoaded', startBinUpdates); 