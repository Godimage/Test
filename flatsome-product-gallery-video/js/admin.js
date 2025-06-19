jQuery(function($) {
    'use strict';

    var video_uploader_frame;
    var thumbnail_uploader_frame;

    // Video Uploader
    $('#fvg_upload_video_button').on('click', function(event) {
        event.preventDefault();
        if (video_uploader_frame) {
            video_uploader_frame.open();
            return;
        }
        video_uploader_frame = wp.media({
            title: 'Select or Upload Video',
            button: { text: 'Use this video' },
            library: { type: 'video' },
            multiple: false
        });
        video_uploader_frame.on('select', function() {
            var attachment = video_uploader_frame.state().get('selection').first().toJSON();
            $('#fvg_video_id').val(attachment.id);
            $('#fvg_video_file_name').text(attachment.filename);
            $('#fvg_remove_video_button').show();
            $('#fvg_upload_video_button').hide();
        });
        video_uploader_frame.open();
    });

    // Remove Video Button
    $('#fvg_remove_video_button').on('click', function(event) {
        event.preventDefault();
        $('#fvg_video_id').val('');
        $('#fvg_video_file_name').text('');
        $(this).hide();
        $('#fvg_upload_video_button').show();
    });

    // Thumbnail Uploader
    $('#fvg_upload_thumbnail_button').on('click', function(event) {
        event.preventDefault();
        if (thumbnail_uploader_frame) {
            thumbnail_uploader_frame.open();
            return;
        }
        thumbnail_uploader_frame = wp.media({
            title: 'Select or Upload Thumbnail Image',
            button: { text: 'Use this image' },
            library: { type: 'image' },
            multiple: false
        });
        thumbnail_uploader_frame.on('select', function() {
            var attachment = thumbnail_uploader_frame.state().get('selection').first().toJSON();
            var thumbnail_url = attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url;
            $('#fvg_thumbnail_id').val(attachment.id);
            $('#fvg_thumbnail_preview').html('<img src="' + encodeURI(thumbnail_url) + '" style="max-width:100%; height:auto;" />');
            $('#fvg_remove_thumbnail_button').show();
            $('#fvg_upload_thumbnail_button').hide();
        });
        thumbnail_uploader_frame.open();
    });

    // Remove Thumbnail Button
    $('#fvg_remove_thumbnail_button').on('click', function(event) {
        event.preventDefault();
        $('#fvg_thumbnail_id').val('');
        $('#fvg_thumbnail_preview').html('');
        $(this).hide();
        $('#fvg_upload_thumbnail_button').show();
    });
});
