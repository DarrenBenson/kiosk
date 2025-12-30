/**
 * Slideshow Display Class
 * Cycles through images with configurable timing
 */
class Slideshow {
    /**
     * Creates a new Slideshow instance
     * @param {number} slideSpeed - Time in milliseconds between transitions (default: 10000)
     */
    constructor(slideSpeed = 10000) {
        this.slideSpeed = slideSpeed;
        this.slideIndex = 0;
        this.slides = document.getElementsByClassName('mySlides');

        if (this.slides.length === 0) {
            console.warn('No slides found with class "mySlides"');
            return;
        }

        this.start();
    }

    /**
     * Shows the next slide and schedules the following transition
     */
    showNext() {
        // Hide all slides
        Array.from(this.slides).forEach(slide => {
            slide.style.display = 'none';
        });

        // Move to next slide, wrapping around
        this.slideIndex = (this.slideIndex + 1) % this.slides.length;

        // Show current slide
        this.slides[this.slideIndex].style.display = 'block';

        // Schedule next transition
        setTimeout(() => this.showNext(), this.slideSpeed);
    }

    /**
     * Starts the slideshow
     */
    start() {
        this.showNext();
    }
}

// Initialize slideshow when DOM is loaded
document.addEventListener('DOMContentLoaded', () => new Slideshow());
