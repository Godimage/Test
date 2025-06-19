/**
 * frontend.js
 *
 * This script provides a custom lightbox implementation for the Flatsome
 * Product Gallery Video plugin. It bypasses the theme's default lightbox
 * trigger to create a dedicated, highly compatible video popup.
 *
 * This approach is more robust than modifying the theme's existing lightbox
 * instance because it does not depend on the theme's internal script timing
 * or configuration. It directly intercepts the user's click, prevents the
 * theme's script from running, and launches a fresh Magnific Popup instance
 * tailored for video playback.
 *
 * @version 2.0.0
 * @author  Expert Analyst
 */
jQuery(function($) {
    'use strict';

    //==========================================================================
    // 1. EVENT DELEGATION
    //==========================================================================
    // We use a delegated event listener attached to the document. This ensures
    // that the click handler works reliably even if the product gallery is
    // loaded dynamically via AJAX after the initial page load.
    //
    // The selector `a[data-fvg-video-url]` is highly specific, targeting only
    // the anchor tags that have been specially prepared by our PHP integration.
    //==========================================================================

    $(document).on('click', 'a[data-fvg-video-url]', function(event) {

        //==========================================================================
        // 2. INTERCEPT AND PREVENT DEFAULTS
        //==========================================================================
        // This is the most critical step for compatibility.
        // - `event.preventDefault()`: Stops the browser from following the link's href.
        // - `event.stopImmediatePropagation()`: Prevents any other JavaScript click
        //   handlers on this element from running. This is what blocks the
        //   Flatsome theme's own lightbox script from firing.
        //==========================================================================

        event.preventDefault();
        event.stopImmediatePropagation();

        //==========================================================================
        // 3. DATA EXTRACTION
        //==========================================================================
        // Retrieve the video URL and configuration flags from the data attributes
        // that our PHP code embedded in the link.
        //==========================================================================

        const $link = $(this);
        const videoUrl = $link.data('fvg-video-url');
        const autoResize = $link.data('fvg-auto-resize'); // Will be `true` or `false`

        // Failsafe: Exit if the video URL is missing for any reason.
        if (!videoUrl) {
            console.error('Flatsome Gallery Video: Video URL not found on clicked element.');
            return;
        }

        //==========================================================================
        // 4. MANUAL LIGHTBOX INITIALIZATION
        //==========================================================================
        // We now manually construct and open our own Magnific Popup instance,
        // giving us full control over its behavior and appearance.
        //==========================================================================

        const magnificPopupConfig = {
            // Define the item to be displayed
            items: {
                src: videoUrl,
            },
            // Set the content type to 'iframe' for video embedding
            type: 'iframe',

            // Add CSS classes for styling and animations
            mainClass: 'mfp-fade fvg-video-lightbox',

            // Callbacks for dynamic class management
            callbacks: {
                open: function() {
                    // This function fires after the popup has been created and is visible.
                    // If auto-resize is enabled, we add a class to the main wrapper.
                    // `this.wrap` is a jQuery object for the `.mfp-wrap` element.
                    if (autoResize === true) {
                        this.wrap.addClass('fvg-lightbox-auto-resize');
                    }
                },
                close: function() {
                    // While the `.mfp-wrap` element is destroyed on close (making this
                    // redundant), explicitly removing the class is good practice.
                    if (autoResize === true) {
                        this.wrap.removeClass('fvg-lightbox-auto-resize');
                    }
                }
            }
        };

        // CRITICAL: If auto-resize is enabled, we must provide custom iframe markup.
        // This markup includes the `.fvg-scaler` div, which is essential for the
        // aspect-ratio CSS to work correctly.
        if (autoResize === true) {
            magnificPopupConfig.iframe = {
                markup: '<div class="mfp-iframe-scaler fvg-scaler">' +
                        '<div class="mfp-close"></div>' +
                        '<iframe class="mfp-iframe" frameborder="0" allowfullscreen></iframe>' +
                        '</div>'
            };
        }

        // Launch the lightbox with our custom configuration.
        $.magnificPopup.open(magnificPopupConfig);

    });
});
