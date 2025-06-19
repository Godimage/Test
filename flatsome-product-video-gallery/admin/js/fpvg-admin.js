jQuery(document).ready(function ($) {
    'use strict';

    // Variable to hold the WordPress media frame.
    let mediaFrame;

    /**
     * Handles the click event for the video upload button.
     */
    $('#fpvg_upload_video_button').on('click', function (e) {
        // Prevent the default button action.
        e.preventDefault();

        // If the media frame already exists, reopen it.
        if (mediaFrame) {
            mediaFrame.open();
            return;
        }

        // Create the media frame.
        mediaFrame = wp.media({
            title: 'Select or Upload Product Video',
            button: {
                text: 'Use this video'
            },
            library: {
                type: 'video' // Only allow video files to be selected.
            },
            multiple: false // Disallow multiple selections.
        });

        /**
         * Callback function for when a video is selected from the media frame.
         */
        mediaFrame.on('select', function () {
            // Get the selected attachment's details.
            const attachment = mediaFrame.state().get('selection').first().toJSON();

            // Set the attachment ID in the hidden input field.
            $('#fpvg_video_id').val(attachment.id);

            // Get the preview wrapper element.
            const previewWrapper = $('.fpvg-video-preview-wrapper');

            // Create the video element for the preview.
            const videoPreview = $('<video>', {
                src: attachment.url,
                controls: true,
                style: 'width:100%; height:auto;'
            });

            // Update the preview area with the new video and ensure it's visible.
            previewWrapper.empty().append(videoPreview).show();

            // Show the 'Remove Video' button.
            $('#fpvg_remove_video_button').show();
        });

        // Finally, open the media frame.
        mediaFrame.open();
    });

    /**
     * Handles the click event for the video removal button.
     */
    $('#fpvg_remove_video_button').on('click', function (e) {
        e.preventDefault();

        // Clear the value of the hidden input field.
        $('#fpvg_video_id').val('');

        // Hide and empty the video preview wrapper.
        $('.fpvg-video-preview-wrapper').hide().empty();

        // Hide the 'Remove Video' button itself.
        $(this).hide();
    });
});
