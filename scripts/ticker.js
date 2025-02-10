// Configuration constants - adjust these values to fix timing issues
const showSpeed = 250;    // Time in ms to show new content (0.25 seconds)
const scrollSpeed = 25000; // Delay between transitions (15 seconds)

/**
 * NewsTicker Class
 * Manages a scrolling news ticker that displays items one at a time
 * and scrolls them horizontally across the screen
 * 
 */
class NewsTicker {
	/**
	 * Initialize the news ticker
	 */
	constructor() {
		this.tickerItems = document.querySelectorAll('.ticker-container ul div');
		
		// Debug check: Verify ticker items exist
		if (this.tickerItems.length === 0) {
			console.error('No ticker items found. Check DOM structure and CSS classes.');
			return;
		}
		
		console.log(`Found ${this.tickerItems.length} ticker items`);
		this.init();
	}

	/**
	 * Set up initial state and start the ticker
	 */
	init() {
		// Set initial active/inactive states for ticker items
		this.tickerItems.forEach((item, i) => {
			if (i === 0) {
				item.classList.add('ticker-active');
				console.log('Set initial active item');
			} else {
				item.classList.add('not-active');
			}
		});

		this.scrollActiveContent();
		this.startTicker();
	}

	/**
	 * Switch to the next news item in the ticker
	 */
	setActiveContent() {
		const activeItem = document.querySelector('.ticker-container ul div.ticker-active');
		
		// Debug check: Verify active item exists
		if (!activeItem) {
			console.error('No active ticker item found');
			return;
		}

		activeItem.classList.remove('ticker-active');
		activeItem.classList.add('remove');

		// Get next item or wrap to first item
		const nextItem = activeItem.nextElementSibling || 
						document.querySelector('.ticker-container ul div:first-child');
		
		// Debug check: Verify next item exists
		if (!nextItem) {
			console.error('Failed to find next ticker item');
			return;
		}

		nextItem.classList.remove('not-active');
		nextItem.classList.add('ticker-active');

		// Reset the previous active item
		activeItem.style.transition = '0s ease-in-out';
		activeItem.classList.remove('remove');
		activeItem.classList.add('not-active', 'finished');
	}

	/**
	 * Apply transition effects to show content
	 */
	showActiveContent() {
		setTimeout(() => {
			this.tickerItems.forEach(item => {
				item.style.transition = `${showSpeed / 1000}s ease-in-out`;
			});
		}, showSpeed);
	}

	/**
	 * Reset the position of finished items
	 */
	resetContentPosition() {
		setTimeout(() => {
			const finishedItem = document.querySelector('.ticker-container ul div.finished');
			if (finishedItem) {
				finishedItem.classList.remove('finished');
				finishedItem.querySelector('li').style.marginLeft = '0';
			}
		}, showSpeed);
	}

	/**
	 * Calculate the width of text for scrolling
	 * @param {string} text - The text to measure
	 * @returns {number} Width of the text in pixels
	 */
	getTextWidth(text) {
		const element = document.createElement('div');
		element.style.cssText = 'position:absolute;float:left;white-space:nowrap;visibility:hidden;font:1.1em "Helvetica Neue",Helvetica,Arial,sans-serif;';
		element.textContent = text;
		document.body.appendChild(element);
		const width = element.offsetWidth;
		document.body.removeChild(element);
		
		// Debug check: Verify reasonable width value
		if (width === 0) {
			console.warn('Text width calculation returned 0. Check text content and font loading.');
		}
		
		return width;
	}

	/**
	 * Animate the scrolling of the active content
	 */
	scrollActiveContent() {
		setTimeout(() => {
			const activeContent = document.querySelector('.ticker-container ul div.ticker-active li');
			const textSpan = document.querySelector('.ticker-container ul div.ticker-active li span');
			const tickerCaption = document.querySelector('.ticker-caption');
			
			// Debug check: Verify all required elements exist
			if (!activeContent || !textSpan || !tickerCaption) {
				console.error('Missing required elements for scrolling', {
					activeContent: !!activeContent,
					textSpan: !!textSpan,
					tickerCaption: !!tickerCaption
				});
				return;
			}

			// Calculate total width needed for scrolling
			const contentLength = this.getTextWidth(textSpan.textContent) + tickerCaption.offsetWidth;
			console.log(`Scrolling content width: ${contentLength}px`);
			
			// Animate using CSS transitions for smooth scrolling
			activeContent.style.transition = `margin-left ${scrollSpeed}ms linear`;
			activeContent.style.marginLeft = `-${contentLength}px`;
			
			this.resetContentPosition();
		}, showSpeed);
	}

	/**
	 * Start the ticker's main loop
	 */
	startTicker() {
		console.log('Starting ticker with intervals:', {
			scrollSpeed,
			showSpeed
		});
		
		setInterval(() => {
			this.setActiveContent();
			setTimeout(() => {
				this.showActiveContent();
				this.scrollActiveContent();
			}, showSpeed);
		}, scrollSpeed);
	}
}

// Initialize the news ticker when the DOM is fully loaded
document.addEventListener('DOMContentLoaded', () => {
	console.log('Initializing news ticker...');
	new NewsTicker();
});

String.prototype.textWidth = function(font) {
	var f = font || '1.1em "Helvetica Neue",Helvetica,Arial,sans-serif',
		obj = $('<div></div>')
			  .text(this)
			  .css({'position': 'absolute', 'float': 'left', 'white-space': 'nowrap', 'visibility': 'hidden', 'font': f})
			  .appendTo($('body')),
		width = obj.width();  
	obj.remove();  
	return width;
  }

