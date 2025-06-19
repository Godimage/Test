/**
 * Flatsome Product Video Gallery - Frontend Script (Decoupled)
 *
 * This script implements a decoupled video injection model. It waits for the DOM to be ready,
 * finds the placeholder div injected by the PHP, and dynamically creates and inserts the
 * video player. This avoids conflicts with the theme's gallery initialization scripts.
 *
 * @package    Flatsome_Product_Video_Gallery
 * @author     Neo
 * @version    1.1
 */
document.addEventListener('DOMContentLoaded', function() {
    'use strict';

    // 1. Find the placeholder element injected by the server.
    const placeholder = document.getElementById('fpvg-video-placeholder');

    // If the placeholder doesn't exist on the page, do nothing.
    if (!placeholder) {
        return;
    }

    // 2. Retrieve the video URL from the placeholder's data attribute.
    const videoUrl = placeholder.dataset.videoUrl;

    // If the URL is missing, log an error and exit.
    if (!videoUrl) {
        console.error('FPVG Error: Video URL not found in placeholder data attribute.');
        return;
    }

    // 3. Dynamically create the <video> element in memory.
    const videoElement = document.createElement('video');
    videoElement.classList.add('fpvg-video');
    videoElement.src = videoUrl;
    videoElement.playsInline = true;   // Essential for good UX on iOS.
    videoElement.loop = true;          // Loop the video.
    videoElement.muted = true;         // Start muted for autoplay policies.
    
    // Add the video element to the placeholder.
    placeholder.appendChild(videoElement);
    
    // Find the parent slide container.
    const slideContainer = placeholder.closest('.woocommerce-product-gallery__image');
    
    if (slideContainer) {
        // Play video when the slide becomes active.
        const observer = new MutationObserver(mutations => {
            mutations.forEach(mutation => {
                if (mutation.attributeName === 'class') {
                    const target = mutation.target;
                    if (target.classList.contains('is-selected')) {
                        videoElement.play().catch(e => console.log("Autoplay prevented"));
                    } else {
                        videoElement.pause();
                        videoElement.currentTime = 0; // Rewind video
                    }
                }
            });
        });

        // Start observing the parent 'flickity-slider' for changes in children
        const gallerySlider = slideContainer.closest('.flickity-slider');
        if (gallerySlider) {
             const slides = gallerySlider.querySelectorAll('.woocommerce-product-gallery__image');
             slides.forEach(slide => {
                if(slide.contains(placeholder)){
                     observer.observe(slide, { attributes: true });
                }
             });
        }
       
        // Initial play check in case the video is the first slide.
        if (slideContainer.classList.contains('is-selected')) {
            videoElement.play().catch(e => console.log("Autoplay prevented"));
        }

        // Prevent lightbox on click
        slideContainer.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
        });

    } else {
        console.warn('FPVG Warning: Could not find parent slide container.');
    }
});
