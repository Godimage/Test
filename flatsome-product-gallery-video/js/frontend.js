jQuery(function($) {
    'use strict';

    // Use a delegated event listener on the body for robustness.
    // This script hooks into Magnific Popup's 'mfpBeforeOpen' event.
    $('body').on('mfpBeforeOpen', function() {

        const mfp = $.magnificPopup.instance;
        const triggerEl = mfp.st.el;

        // Check if the clicked link has our special video data attribute.
        if (triggerEl && triggerEl.data('fvg-video-url')) {

            // It's our video link. Modify the lightbox settings on the fly.
            const videoUrl = triggerEl.data('fvg-video-url');
            const autoResize = triggerEl.data('fvg-auto-resize');
            const currentItem = mfp.currItem;

            // 1. Override the source (src) to be the video URL.
            currentItem.src = videoUrl;

            // 2. Change the content type from 'image' to 'iframe'.
            currentItem.type = 'iframe';

            // 3. Add custom classes for styling the video player.
            mfp.st.mainClass += ' fvg-video-lightbox';

            if (autoResize === true) {
                mfp.st.mainClass += ' fvg-lightbox-auto-resize';

                // Inject markup for responsive scaling.
                mfp.st.iframe.markup = '<div class="mfp-iframe-scaler fvg-scaler">' +
                                       '<div class="mfp-close"></div>' +
                                       '<iframe class="mfp-iframe" frameborder="0" allowfullscreen></iframe>' +
                                       '</div>';
            }
        }
    });
});
