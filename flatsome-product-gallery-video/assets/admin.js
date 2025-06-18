jQuery(document).ready(function($) {
    var fpgv_video_frame;
    var fpgv_thumbnail_frame;
    
    // Video upload
    $('body').on('click', '.fpgv-upload-video', function(e) {
        e.preventDefault();
        
        if (fpgv_video_frame) {
            fpgv_video_frame.open();
            return;
        }
        
        fpgv_video_frame = wp.media({
            title: 'Select Product Video',
            button: {
                text: 'Use this video'
            },
            library: {
                type: 'video'
            },
            multiple: false
        });
        
        fpgv_video_frame.on('select', function() {
            var attachment = fpgv_video_frame.state().get('selection').first().toJSON();
            
            $('#fpgv_video_id').val(attachment.id);
            
            var videoHtml = '<video width="100%" height="150" controls>' +
                          '<source src="' + attachment.url + '" type="video/mp4">' +
                          '</video>' +
                          '<button type="button" class="button fpgv-remove-video">Remove Video</button>';
            
            $('.fpgv-video-preview').html(videoHtml).show();
            $('.fpgv-video-upload-btn').hide();
        });
        
        fpgv_video_frame.open();
    });
    
    // Thumbnail upload
    $('body').on('click', '.fpgv-upload-thumbnail', function(e) {
        e.preventDefault();
        
        if (fpgv_thumbnail_frame) {
            fpgv_thumbnail_frame.open();
            return;
        }
        
        fpgv_thumbnail_frame = wp.media({
            title: 'Select Video Thumbnail',
            button: {
                text: 'Use this image'
            },
            library: {
                type: 'image'
            },
            multiple: false
        });
        
        fpgv_thumbnail_frame.on('select', function() {
            var attachment = fpgv_thumbnail_frame.state().get('selection').first().toJSON();
            
            $('#fpgv_thumbnail_id').val(attachment.id);
            
            var thumbnailHtml = '<img src="' + attachment.url + '" style="max-width: 100%; height: auto;">' +
                              '<button type="button" class="button fpgv-remove-thumbnail">Remove Thumbnail</button>';
            
            $('.fpgv-thumbnail-preview').html(thumbnailHtml).show();
            $('.fpgv-thumbnail-upload-btn').hide();
        });
        
        fpgv_thumbnail_frame.open();
    });
    
    // Remove video
    $('body').on('click', '.fpgv-remove-video', function(e) {
        e.preventDefault();
        
        $('#fpgv_video_id').val('');
        $('.fpgv-video-preview').hide();
        $('.fpgv-video-upload-btn').show();
    });
    
    // Remove thumbnail
    $('body').on('click', '.fpgv-remove-thumbnail', function(e) {
        e.preventDefault();
        
        $('#fpgv_thumbnail_id').val('');
        $('.fpgv-thumbnail-preview').hide();
        $('.fpgv-thumbnail-upload-btn').show();
    });
});
