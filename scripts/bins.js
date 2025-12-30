/**
 * Bin Collection Display Module
 * Fetches and displays bin collection information from iCal-based API
 */
const BinCollection = {
    /**
     * Fetches bin collection data from the API
     */
    async fetch() {
        try {
            const response = await fetch('/api/bins.php');
            const data = await response.json();

            if (data.error) {
                console.warn('Bin collection warning:', data.error);
            }

            this.update(data);
        } catch (error) {
            console.error('Error fetching bin collection data:', error);
        }
    },

    /**
     * Updates the DOM with bin collection data
     * @param {Object} data - Bin collection data from API
     */
    update(data) {
        // Update date display
        const dateEl = document.getElementById('bin-date');
        if (dateEl) {
            // Show date with days until collection
            let dateText = data.date;
            if (data.isToday) {
                dateText += ' (Today!)';
                dateEl.classList.add('collection-today');
            } else if (data.daysUntil === 1) {
                dateText += ' (Tomorrow)';
                dateEl.classList.remove('collection-today');
            } else {
                dateEl.classList.remove('collection-today');
            }
            dateEl.textContent = dateText;
        }

        // Update bin icons
        const bins = ['green', 'grey', 'brown'];
        bins.forEach(bin => {
            const el = document.getElementById(`${bin}-bin`);
            if (el) {
                el.classList.toggle('active', data.bins[bin] === true);
            }
        });
    },

    /**
     * Initializes bin collection updates
     */
    init() {
        this.fetch();
        // Update every hour
        setInterval(() => this.fetch(), 3600000);
    }
};

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => BinCollection.init());
