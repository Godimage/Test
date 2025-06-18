jQuery(document).ready(function($) {
    'use strict';
    
    var ProductVideoAdmin = {
        
        mediaUploader: null,
        currentField: null,
        
        init: function() {
            this.bindEvents();
        },
        
        bindEvents: function() {
            $('#upload_video_button').on('click', this.openVideoUploader.bind(this));
            $('#upload_thumbnail_button').on('click', this.openThumbnailUploader.bind(this));
            $('#remove_video_button').on('click', this.removeVideo.bind(this));
            $('#remove_thumbnail_button').on('click', this.removeThumbnail.bind(this));
        },
        
        openVideoUploader: function(e) {
            e.preventDefault();
            
            this.currentField = 'video';
            
            if (this.mediaUploader) {
                this.mediaUploader.open();
                return;
            }
            
            this.mediaUploader = wp.media({
                title: 'Choose Product Video',
                button: {
                    text: 'Use this video'
                },
                library: {
                    type: ['video']
                },
                multiple: false
            });
            
            this.mediaUploader.on('select', this.handleMediaSelection.bind(this));
            this.mediaUploader.open();
        },
        
        openThumbnailUploader: function(e) {
            e.preventDefault();
            
            this.currentField = 'thumbnail';
            
            if (this.mediaUploader) {
                this.mediaUploader.open();
                return;
            }
            
            this.mediaUploader = wp.media({
                title: 'Choose Video Thumbnail',
                button: {
                    text: 'Use this image'
                },
                library: {
                    type: ['image']
                },
                multiple: false
            });
            
            this.mediaUploader.on('select', this.handleMediaSelection.bind(this));
            this.mediaUploader.open();
        },
        
        handleMediaSelection: function() {
            var attachment = this.mediaUploader.state().get('selection').first().toJSON();
            
            if (this.currentField === 'video') {
                $('#product_video_url').val(attachment.url);
                this.showRemoveVideoButton();
            } else if (this.currentField === 'thumbnail') {
                $('#product_video_thumbnail').val(attachment.url);
                this.showThumbnailPreview(attachment.url);
                this.showRemoveThumbnailButton();
            }
        },
        
        removeVideo: function(e) {
            e.preventDefault();
            
            if (confirm('Are you sure you want to remove the video?')) {
                $('#product_video_url').val('');
                this.hideRemoveVideoButton();
            }
        },
        
        removeThumbnail: function(e) {
            e.preventDefault();
            
            if (confirm('Are you sure you want to remove the thumbnail?')) {
                $('#product_video_thumbnail').val('');
                this.hideThumbnailPreview();
                this.hideRemoveThumbnailButton();
            }
        },
        
        showRemoveVideoButton: function() {
            if ($('#remove_video_button').length === 0) {
                $('#upload_video_button').after('<input type="button" id="remove_video_button" class="button" value="Remove Video" style="margin-left: 5px; background: #dc3545; color: white;" />');
                $('#remove_video_button').on('click', this.removeVideo.bind(this));
            }
        },
        
        hideRemoveVideoButton: function() {
            $('#remove_video_button').remove();
        },
        
        showRemoveThumbnailButton: function() {
            if ($('#remove_thumbnail_button').length === 0) {
                $('#upload_thumbnail_button').after('<input type="button" id="remove_thumbnail_button" class="button" value="Remove Thumbnail" style="margin-left: 5px; background: #dc3545; color: white;" />');
                $('#remove_thumbnail_button').on('click', this.removeThumbnail.bind(this));
            }
        },
        
        hideRemoveThumbnailButton: function() {
            $('#remove_thumbnail_button').remove();
        },
        
        showThumbnailPreview: function(url) {
            var $preview = $('#product_video_thumbnail').siblings('img');
            if ($preview.length === 0) {
                $('#product_video_thumbnail').parent().append('<br><img src="' + url + '" style="max-width: 150px; margin-top: 10px;" />');
            } else {
                $preview.attr('src', url).show();
            }
        },
        
        hideThumbnailPreview: function() {
            $('#product_video_thumbnail').siblings('img').hide();
        }
    };
    
    // Initialize
    ProductVideoAdmin.init();
});