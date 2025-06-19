/**
 * Flatsome Product Video Gallery - Frontend JavaScript
 * Handles video playback and gallery integration
 */

(function($) {
    'use strict';
    
    // Main FPVG object
    window.FPVG = {
        videos: [],
        currentVideo: null,
        isMobile: false,
        
        init: function() {
            this.loadVideoData();
            this.detectMobile();
            this.bindEvents();
            this.adjustVideoSizes();
        },
        
        loadVideoData: function() {
            var dataElement = $('#fpvg-video-data');
            if (dataElement.length) {
                try {
                    this.videos = JSON.parse(dataElement.text());
                } catch (e) {
                    console.warn('FPVG: Could not parse video data');
                    this.videos = [];
                }
            }
            
            // Also check localized data
            if (typeof fpvg_data !== 'undefined' && fpvg_data.videos) {
                this.videos = fpvg_data.videos;
                this.isMobile = fpvg_data.is_mobile;
            }
        },
        
        detectMobile: function() {
            this.isMobile = window.innerWidth <= 768 || /Android|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
        },
        
        bindEvents: function() {
            var self = this;
            
            // Video play/pause buttons
            $(document).on('click', '.fpvg-play-button', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                var videoId = $(this).data('video-id');
                var video = document.getElementById(videoId);
                
                if (video) {
                    self.toggleVideo(video, $(this));
                }
            });
            
            // Video click (for play/pause)
            $(document).on('click', '.fpvg-gallery-video', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                var $button = $(this).siblings('.fpvg-video-overlay').find('.fpvg-play-button');
                self.toggleVideo(this, $button);
            });
            
            // Prevent lightbox on video items
            $(document).on('click', '.fpvg-video-item a', function(e) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            });
            
            // Handle video ended
            $(document).on('ended', '.fpvg-gallery-video', function() {
                var $button = $(this).siblings('.fpvg-video-overlay').find('.fpvg-play-button');
                self.showPlayIcon($button);
                $(this).closest('.fpvg-video-item').removeClass('playing');
            });
            
            // Pause other videos when one starts playing
            $(document).on('play', '.fpvg-gallery-video', function() {
                self.pauseOtherVideos(this);
                $(this).closest('.fpvg-video-item').addClass('playing');
            });
            
            // Handle window resize
            $(window).on('resize', function() {
                self.detectMobile();
                self.adjustVideoSizes();
            });
        },
        
        toggleVideo: function(video, $button) {
            if (video.paused) {
                this.playVideo(video, $button);
            } else {
                this.pauseVideo(video, $button);
            }
        },
        
        playVideo: function(video, $button) {
            var self = this;
            
            // Pause other videos first
            this.pauseOtherVideos(video);
            
            // Play the video
            var playPromise = video.play();
            
            if (playPromise !== undefined) {
                playPromise.then(function() {
                    self.showPauseIcon($button);
                    self.currentVideo = video;
                }).catch(function(error) {
                    console.warn('FPVG: Video play failed:', error);
                });
            }
        },
        
        pauseVideo: function(video, $button) {
            video.pause();
            this.showPlayIcon($button);
            $(video).closest('.fpvg-video-item').removeClass('playing');
            
            if (this.currentVideo === video) {
                this.currentVideo = null;
            }
        },
        
        pauseOtherVideos: function(currentVideo) {
            var self = this;
            
            $('.fpvg-gallery-video').each(function() {
                if (this !== currentVideo && !this.paused) {
                    this.pause();
                    var $button = $(this).siblings('.fpvg-video-overlay').find('.fpvg-play-button');
                    self.showPlayIcon($button);
                    $(this).closest('.fpvg-video-item').removeClass('playing');
                }
            });
        },
        
        showPlayIcon: function($button) {
            $button.find('.fpvg-play-icon').show();
            $button.find('.fpvg-pause-icon').hide();
            $button.attr('aria-label', 'Play video');
        },
        
        showPauseIcon: function($button) {
            $button.find('.fpvg-play-icon').hide();
            $button.find('.fpvg-pause-icon').show();
            $button.attr('aria-label', 'Pause video');
        },
        
        adjustVideoSizes: function() {
            var self = this;
            
            // Wait for images to load
            setTimeout(function() {
                $('.fpvg-video-item').each(function() {
                    var $videoItem = $(this);
                    var $video = $videoItem.find('.fpvg-gallery-video');
                    var $container = $videoItem.find('.fpvg-video-container');
                    
                    if ($video.length && $container.length) {
                        // Ensure video fits properly in container
                        $video.css({
                            'width': '100%',
                            'height': '100%',
                            'object-fit': 'cover'
                        });
                        
                        // Make overlay active
                        $videoItem.find('.fpvg-video-overlay').addClass('active');
                    }
                });
            }, 500);
        }
    };
    
    // Initialize when DOM is ready
    $(document).ready(function() {
        // Wait a bit for other scripts to load
        setTimeout(function() {
            if (typeof FPVG !== 'undefined') {
                FPVG.init();
            }
        }, 100);
    });
    
})(jQuery);

