/**
 * Admin script for the Flatsome Product Gallery Video plugin.
 *
 * Handles the media uploader functionality for selecting and removing
 * the product video and its custom thumbnail.
 *
 * @version 5.0.0
 */
(function($) {
    'use strict';

    $(document).ready(function() {

        // Store the original button text for resetting
        const originalVideoButtonText = $('#fvg_upload_video_button').text();
        const originalThumbButtonText = $('#fvg_upload_thumbnail_button').text();

        // --- INITIAL UI STATE ---
        // On page load, check if values exist and update button text for clarity.
        // The PHP handles the 'Remove' button visibility, but this ensures the 'Upload' button
        // text is also correct ('Change' vs. 'Upload').
        if ($('#_fvg_video_id').val()) {
            $('#fvg_upload_video_button').text('Change Video');
        }

        if ($('#_fvg_thumbnail_id').val()) {
            $('#fvg_upload_thumbnail_button').text('Change Thumbnail');
        }

        // =========================================================================
        // VIDEO UPLOADER LOGIC
        // =========================================================================

        let videoFrame;

        // 1. Handle Video Upload Button Click
        $('#fvg_upload_video_button').on('click', function(event) {
            event.preventDefault();

            // Create a new media frame, or open the existing one.
            if (videoFrame) {
                videoFrame.open();
                return;
            }

            // Create the media frame.
            videoFrame = wp.media({
                title: 'Select or Upload Video',
                button: {
                    text: 'Use this video'
                },
                library: {
                    type: 'video' // Filter for video files
                },
                multiple: false // Disallow multiple selections
            });

            // When a video is selected, run a callback.
            videoFrame.on('select', function() {
                // Get the video's attachment details.
                const attachment = videoFrame.state().get('selection').first().toJSON();

                // Populate the hidden input field with the video ID.
                $('#_fvg_video_id').val(attachment.id);

                // Update the preview area to show the filename.
                $('#fvg_video_preview').html('<span class="fvg-file-name">' + attachment.filename + '</span>');

                // Update the UI state.
                $('#fvg_upload_video_button').text('Change Video');
                $('#fvg_remove_video_button').show();
            });

            // Finally, open the media frame.
            videoFrame.open();
        });

        // 2. Handle Video Remove Button Click
        $('#fvg_remove_video_button').on('click', function(event) {
            event.preventDefault();

            // Clear the hidden input field.
            $('#_fvg_video_id').val('');

            // Reset the preview area to its placeholder.
            $('#fvg_video_preview').html('<span class="fvg-placeholder">No video selected</span>');

            // Reset the UI state.
            $('#fvg_upload_video_button').text(originalVideoButtonText);
            $(this).hide();
        });


        // =========================================================================
        // THUMBNAIL UPLOADER LOGIC
        // =========================================================================

        let thumbnailFrame;

        // 3. Handle Thumbnail Upload Button Click
        $('#fvg_upload_thumbnail_button').on('click', function(event) {
            event.preventDefault();

            if (thumbnailFrame) {
                thumbnailFrame.open();
                return;
            }

            thumbnailFrame = wp.media({
                title: 'Select or Upload Thumbnail',
                button: {
                    text: 'Use this thumbnail'
                },
                library: {
                    type: 'image' // Filter for image files
                },
                multiple: false
            });

            thumbnailFrame.on('select', function() {
                const attachment = thumbnailFrame.state().get('selection').first().toJSON();
                const thumbnailUrl = attachment.sizes && attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url;

                // Populate the hidden input field with the thumbnail ID.
                $('#_fvg_thumbnail_id').val(attachment.id);

                // Update the preview area with the image.
                $('#fvg_thumbnail_preview').html('<img src="' + thumbnailUrl + '" alt="Thumbnail Preview" style="max-width:100%; height:auto;">');

                // Update the UI state.
                $('#fvg_upload_thumbnail_button').text('Change Thumbnail');
                $('#fvg_remove_thumbnail_button').show();
            });

            thumbnailFrame.open();
        });

        // 4. Handle Thumbnail Remove Button Click
        $('#fvg_remove_thumbnail_button').on('click', function(event) {
            event.preventDefault();

            // Clear the hidden input field.
            $('#_fvg_thumbnail_id').val('');

            // Reset the preview area.
            $('#fvg_thumbnail_preview').html('<span class="fvg-placeholder">No thumbnail selected</span>');

            // Reset the UI state.
            $('#fvg_upload_thumbnail_button').text(originalThumbButtonText);
            $(this).hide();
        });

    });

})(jQuery);
