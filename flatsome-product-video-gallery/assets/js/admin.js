jQuery(document).ready(function($) {
    'use strict';
    
    var videosData = [];
    
    // Initialize
    init();
    
    function init() {
        // Load existing videos data
        loadVideosData();
        
        // Bind events
        bindEvents();
        
        // Setup drag and drop
        setupDragDrop();
    }
    
    function loadVideosData() {
        var dataElement = $('#fpvg-videos-data');
        if (dataElement.length && dataElement.val()) {
            try {
                videosData = JSON.parse(dataElement.val());
            } catch (e) {
                videosData = [];
            }
        }
    }
    
    function bindEvents() {
        // Upload button click
        $('#fpvg-upload-btn').on('click', function() {
            $('#fpvg-video-upload').click();
        });
        
        // File input change
        $('#fpvg-video-upload').on('change', function() {
            var files = this.files;
            if (files.length > 0) {
                uploadVideo(files[0]);
            }
        });
        
        // Delete video
        $(document).on('click', '.fpvg-delete-btn', function() {
            if (confirm(fpvg_admin.strings.delete_confirm)) {
                deleteVideo($(this));
            }
        });
        
        // Position change
        $(document).on('change', '.fpvg-position-input', function() {
            updateVideoPosition($(this));
        });
        
        // Form submit
        $('form#post').on('submit', function() {
            updateVideosData();
        });
    }
    
    function setupDragDrop() {
        var uploadArea = $('.fpvg-upload-area');
        
        uploadArea.on('dragover dragenter', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).addClass('dragover');
        });
        
        uploadArea.on('dragleave dragend', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).removeClass('dragover');
        });
        
        uploadArea.on('drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).removeClass('dragover');
            
            var files = e.originalEvent.dataTransfer.files;
            if (files.length > 0) {
                uploadVideo(files[0]);
            }
        });
    }
    
    function uploadVideo(file) {
        // Validate file
        if (!validateFile(file)) {
            return;
        }
        
        // Show progress
        showUploadProgress();
        
        // Create form data
        var formData = new FormData();
        formData.append('action', 'fpvg_upload_video');
        formData.append('nonce', fpvg_admin.upload_nonce);
        formData.append('video_file', file);
        
        // Upload via AJAX
        $.ajax({
            url: fpvg_admin.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            xhr: function() {
                var xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener('progress', function(e) {
                    if (e.lengthComputable) {
                        var percentComplete = (e.loaded / e.total) * 100;
                        updateUploadProgress(percentComplete);
                    }
                }, false);
                return xhr;
            },
            success: function(response) {
                hideUploadProgress();
                
                if (response.success) {
                    addVideoToList(response.data);
                    $('#fpvg-video-upload').val(''); // Clear file input
                } else {
                    alert(response.data || fpvg_admin.strings.upload_error);
                }
            },
            error: function() {
                hideUploadProgress();
                alert(fpvg_admin.strings.upload_error);
            }
        });
    }
    
    function validateFile(file) {
        // Check file size
        if (file.size > fpvg_admin.max_file_size) {
            alert(fpvg_admin.strings.file_too_large);
            return false;
        }
        
        // Check file type
        var fileExtension = file.name.split('.').pop().toLowerCase();
        if (fpvg_admin.allowed_types.indexOf(fileExtension) === -1) {
            alert(fpvg_admin.strings.invalid_type);
            return false;
        }
        
        return true;
    }
    
    function showUploadProgress() {
        $('#fpvg-upload-progress').show();
        updateUploadProgress(0);
    }
    
    function updateUploadProgress(percent) {
        $('.fpvg-progress-fill').css('width', percent + '%');
        $('.fpvg-progress-text').text('Uploading... ' + Math.round(percent) + '%');
    }
    
    function hideUploadProgress() {
        $('#fpvg-upload-progress').hide();
    }
    
    function addVideoToList(videoData) {
        // Add to videos array
        var newVideo = {
            url: videoData.url,
            filename: videoData.filename,
            type: videoData.type,
            size: videoData.size,
            position: getNextPosition()
        };
        
        videosData.push(newVideo);
        
        // Create HTML element
        var videoHtml = createVideoItemHtml(newVideo, videosData.length - 1);
        $('#fpvg-video-list').append(videoHtml);
        
        // Update hidden input
        updateVideosData();
    }
    
    function createVideoItemHtml(video, index) {
        var html = '<div class="fpvg-video-item" data-index="' + index + '">';
        html += '<video class="fpvg-video-preview" controls preload="metadata">';
        html += '<source src="' + video.url + '" type="video/' + video.type + '">';
        html += '</video>';
        
        html += '<div class="fpvg-video-controls">';
        html += '<label>Position: ';
        html += '<input type="number" class="fpvg-position-input" value="' + video.position + '" min="1" max="99">';
        html += '</label>';
        html += '<button type="button" class="fpvg-delete-btn" data-video-url="' + video.url + '">Delete</button>';
        html += '</div>';
        
        html += '<div class="fpvg-video-info">';
        html += '<small>File: ' + video.filename + ' | Size: ' + formatFileSize(video.size) + '</small>';
        html += '</div>';
        
        html += '</div>';
        
        return html;
    }
    
    function deleteVideo($button) {
        var videoUrl = $button.data('video-url');
        var $videoItem = $button.closest('.fpvg-video-item');
        var index = parseInt($videoItem.data('index'));
        
        // Remove from server
        $.ajax({
            url: fpvg_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'fpvg_delete_video',
                nonce: fpvg_admin.delete_nonce,
                video_url: videoUrl
            },
            success: function(response) {
                if (response.success) {
                    // Remove from array
                    videosData.splice(index, 1);
                    
                    // Remove from DOM
                    $videoItem.remove();
                    
                    // Update indices
                    updateVideoIndices();
                    
                    // Update hidden input
                    updateVideosData();
                }
            }
        });
    }
    
    function updateVideoPosition($input) {
        var $videoItem = $input.closest('.fpvg-video-item');
        var index = parseInt($videoItem.data('index'));
        var newPosition = parseInt($input.val());
        
        if (videosData[index]) {
            videosData[index].position = newPosition;
            updateVideosData();
        }
    }
    
    function updateVideoIndices() {
        $('#fpvg-video-list .fpvg-video-item').each(function(index) {
            $(this).attr('data-index', index);
        });
    }
    
    function updateVideosData() {
        $('#fpvg-videos-data').val(JSON.stringify(videosData));
    }
    
    function getNextPosition() {
        var maxPosition = 0;
        videosData.forEach(function(video) {
            if (video.position > maxPosition) {
                maxPosition = video.position;
            }
        });
        return maxPosition + 1;
    }
    
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        var k = 1024;
        var sizes = ['Bytes', 'KB', 'MB', 'GB'];
        var i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
});

