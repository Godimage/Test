/**
 * Flatsome Product Video Gallery - Frontend Script
 *
 * This script provides the client-side functionality for the video added to the
 * Flatsome product gallery. It enables play/pause on click and intelligently
 * integrates with the slider to prevent the default lightbox action and manage
 * video state during slide navigation.
 *
 * @package    Flatsome_Product_Video_Gallery
 * @author     Neo
 * @version    1.0
 */
jQuery(document).ready(function($) {
    'use strict';

    // Define key selectors for the gallery components. Using constants makes the code
    // easier to read and maintain.
    const galleryContainerSelector = '.woocommerce-product-gallery'; // The top-level gallery container.
    const sliderSelector = '.product-gallery-slider'; // The Flickity slider element used by Flatsome.
    const videoWrapperSelector = '.woocommerce-product-gallery__image a:has(.fpvg-video)'; // The specific 'a' tag that wraps our video.

    /**
     * Binds a click event handler to the video's wrapper element.
     *
     * This function uses event delegation, attaching a single listener to the stable
     * `galleryContainerSelector`. This is a robust approach that ensures the handler
     * works reliably even as the slider manipulates the DOM (e.g., during navigation).
     * It correctly identifies clicks on the video slide and prevents the default
     * lightbox behavior.
     */
    $(galleryContainerSelector).on('click', videoWrapperSelector, function(event) {

        // 1. Prevent Flatsome's Default Lightbox Behavior
        // This is the most critical step. We stop the browser from following the
        // link's href and prevent the click event from bubbling up to other
        // scripts (like Flatsome's Magnific Popup) that would otherwise open a lightbox.
        event.preventDefault();
        event.stopPropagation();

        // 2. Locate the Video Element
        // From the clicked wrapper (`this`), find the actual <video> element.
        // We use .get(0) to retrieve the raw DOM element from the jQuery object,
        // as the play() and pause() methods are part of the HTMLVideoElement API.
        const videoElement = $(this).find('.fpvg-video').get(0);

        // Safety check: If for some reason the video element isn't found, do nothing.
        if (!videoElement) {
            console.warn('FPVG: Video element not found inside the clicked slide.');
            return;
        }

        // 3. Toggle Play/Pause Logic
        // Check the native 'paused' property of the video element to determine its current
        // state and call the appropriate method to toggle it.
        if (videoElement.paused) {
            // If the video is paused or has not started, play it.
            videoElement.play();
        } else {
            // If the video is currently playing, pause it.
            videoElement.pause();
        }
    });

    /**
     * Slider Integration: Pause video when navigating away from its slide.
     *
     * This provides a better user experience by ensuring the video doesn't continue
     * to play in the background after the user navigates to a different image.
     * We hook into the 'change' event of the Flickity slider that powers the gallery.
     */
    const flickitySlider = $(sliderSelector).data('flickity');

    if (flickitySlider) {
        flickitySlider.on('change', function() {
            // When any slide change occurs, find our video element.
            const videoElement = $(galleryContainerSelector).find('.fpvg-video').get(0);

            // If the video element exists and is currently playing, pause it.
            if (videoElement && !videoElement.paused) {
                videoElement.pause();
            }
        });
    }
});
