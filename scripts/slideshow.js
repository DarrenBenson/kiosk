// Time in milliseconds between slide transitions
const slideSpeed = 10000;
let slideIndex = 0;

/**
 * Handles the slideshow functionality by cycling through slides
 * Each slide is hidden and the next one is shown in sequence
 * 
 * Debug tips:
 * - If no slides appear, check if elements with class "mySlides" exist
 * - If slides don't transition, verify slideSpeed value
 * - If slides are out of order, check the DOM structure
 */
function showSlides() {
    // Get all slides and hide them
    const slides = document.getElementsByClassName("mySlides");
    
    // Debug check: Verify slides were found
    if (slides.length === 0) {
        console.warn('No slides found with class "mySlides"');
        return;
    }

    Array.from(slides).forEach(slide => {
        slide.style.display = "none";  
    });

    // Move to next slide, wrapping around to start if at the end
    slideIndex = (slideIndex + 1) % slides.length;
    
    slides[slideIndex].style.display = "block";  

    // Schedule next slide transition
    setTimeout(showSlides, slideSpeed); 
}

// Initialize slideshow when DOM is fully loaded
document.addEventListener('DOMContentLoaded', () => {
    showSlides();
});