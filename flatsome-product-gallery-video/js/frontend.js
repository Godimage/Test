jQuery(function($) {
    'use strict';

    /**
     * @description Handles the frontend logic for the Flatsome Product Gallery Video plugin.
     * @summary Replaces the main product image with a video when a video thumbnail is clicked.
     *          Prevents the default Flatsome lightbox from opening for video links.
     */

    // --- Configuration ---
    const galleryWrapperSelector = '.woocommerce-product-gallery__wrapper';
    const videoLinkSelector = '.fvg-video-link';
    const mainImageSlideSelector = '.woocommerce-product-gallery__image:first-child';

    /**
     * Event handler for clicking the video thumbnail.
     *
     * This function uses a delegated event listener attached to the gallery wrapper
     * for maximum reliability, even if the gallery content is dynamically updated.
     */
    $(galleryWrapperSelector).on('click', videoLinkSelector, function(event) {
        // --- 1. Prevent Default Behavior ---
        // Stop the link's default action (e.g., navigating to '#').
        event.preventDefault();
        // Critically, stop the event from bubbling up to other listeners,
        // such as the Flatsome theme's lightbox script. This is the key
        // to preventing the lightbox from appearing over our video.
        event.stopImmediatePropagation();

        const $videoLink = $(this);

        // --- 2. Extract Data from Thumbnail ---
        const videoUrl = $videoLink.data('fvg-video-url');
        const autoResize = $videoLink.data('fvg-auto-resize');

        // Failsafe: exit if the video URL is missing.
        if (!videoUrl) {
            console.error('Flatsome Video Gallery: Video URL not found in data attribute.');
            return;
        }

        // --- 3. Find the Target Slide for Video Injection ---
        const $mainImageSlide = $(galleryWrapperSelector).find(mainImageSlideSelector);

        // Failsafe: exit if the main gallery slide isn't found.
        if (!$mainImageSlide.length) {
            console.error('Flatsome Video Gallery: Main product image slide not found.');
            return;
        }

        // --- 4. Create the Video Element ---
        // Create a <video> element using jQuery.
        const $videoElement = $('<video>', {
            controls: true,    // Show player controls.
            autoplay: true,    // Start playing immediately.
            playsinline: true, // Essential for iOS to play video within the page.
            'class': 'fvg-injected-video'
        });

        // Apply auto-resize styles if the option is enabled.
        if (autoResize === 'yes') {
            $videoElement.css({
                'width': '100%',
                'height': 'auto',
                'object-fit': 'cover' // Ensures video covers the area nicely.
            });
        }

        // Create the <source> element for the video file.
        const $sourceElement = $('<source>', {
            src: videoUrl,
            type: 'video/mp4' // Assuming MP4, the most common format.
        });

        // Add the source to the video element.
        $videoElement.append($sourceElement);
        // Add fallback text for unsupported browsers.
        $videoElement.append('Your browser does not support the video tag.');

        // --- 5. Inject the Video ---
        // Replace the content of the main image slide (which is usually an <img> tag)
        // with our newly created video element.
        $mainImageSlide.html($videoElement);
    });

    /**
     * Note on Reverting to Images:
     *
     * By design, this script does NOT handle clicks on regular image thumbnails.
     * Because `event.stopImmediatePropagation()` is only called for our specific
     * `fvg-video-link`, the Flatsome theme's native gallery script will handle
     * clicks on all other thumbnails as usual. This allows the theme to correctly
     * take over and display the selected image, effectively replacing the video
     * without any extra code needed here.
     */
});
