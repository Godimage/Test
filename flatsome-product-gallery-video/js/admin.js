/**
 * admin.js
 *
 * Handles the WordPress Media Uploader functionality for the 
 * "Product Gallery Video" meta box on the product admin screen.
 * This script requires jQuery and the `wp.media` object.
 */
jQuery(function($) {
    'use strict';

    // Reusable variables for the media uploader frames
    var video_uploader_frame;
    var thumbnail_uploader_frame;

    /**
     * Video Uploader Section
     */
    $('#fvg_upload_video_button').on('click', function(event) {
        event.preventDefault();

        // If the media frame already exists, reopen it.
        if (video_uploader_frame) {
            video_uploader_frame.open();
            return;
        }

        // Create the media frame.
        video_uploader_frame = wp.media({
            title: 'Select or Upload Video',
            button: {
                text: 'Use this video'
            },
            library: {
                type: 'video' // Filter the library to show only video files
            },
            multiple: false // Disallow multiple selections
        });

        // When a video is selected, run the callback.
        video_uploader_frame.on('select', function() {
            var attachment = video_uploader_frame.state().get('selection').first().toJSON();

            // Populate the hidden input field with the selected video's attachment ID.
            $('#fvg_video_id').val(attachment.id);

            // Display the filename of the selected video.
            $('#fvg_video_file_name').text(attachment.filename);

            // Update button visibility.
            $('#fvg_remove_video_button').show();
            $('#fvg_upload_video_button').hide();
        });

        // Open the media uploader frame.
        video_uploader_frame.open();
    });

    /**
     * Remove Video Button
     */
    $('#fvg_remove_video_button').on('click', function(event) {
        event.preventDefault();

        // Clear the video ID input and filename display.
        $('#fvg_video_id').val('');
        $('#fvg_video_file_name').text('');

        // Update button visibility.
        $(this).hide();
        $('#fvg_upload_video_button').show();
    });

    /**
     * Thumbnail Uploader Section
     */
    $('#fvg_upload_thumbnail_button').on('click', function(event) {
        event.preventDefault();

        if (thumbnail_uploader_frame) {
            thumbnail_uploader_frame.open();
            return;
        }

        thumbnail_uploader_frame = wp.media({
            title: 'Select or Upload Thumbnail Image',
            button: {
                text: 'Use this image'
            },
            library: {
                type: 'image' // Filter the library to show only image files
            },
            multiple: false
        });

        thumbnail_uploader_frame.on('select', function() {
            var attachment = thumbnail_uploader_frame.state().get('selection').first().toJSON();

            // Determine the best thumbnail size to use for the preview.
            var thumbnail_url = attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url;

            // Populate the hidden input field with the selected image's ID.
            $('#fvg_thumbnail_id').val(attachment.id);

            // Display a preview of the selected thumbnail.
            $('#fvg_thumbnail_preview').html('<img src="' + encodeURI(thumbnail_url) + '" style="max-width:100%; height:auto;" />');

            // Update button visibility.
            $('#fvg_remove_thumbnail_button').show();
            $('#fvg_upload_thumbnail_button').hide();
        });

        thumbnail_uploader_frame.open();
    });

    /**
     * Remove Thumbnail Button
     */
    $('#fvg_remove_thumbnail_button').on('click', function(event) {
        event.preventDefault();

        // Clear the thumbnail ID input and the image preview.
        $('#fvg_thumbnail_id').val('');
        $('#fvg_thumbnail_preview').html('');

        // Update button visibility.
        $(this).hide();
        $('#fvg_upload_thumbnail_button').show();
    });

});
