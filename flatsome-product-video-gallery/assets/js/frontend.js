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
        flickityInstance: null,
        isMobile: false,
        
        init: function() {
            this.loadVideoData();
            this.detectMobile();
            this.bindEvents();
            this.integrateWithFlickity();
            this.setupVideoThumbnails();
            this.handleMobileGallery();
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
            
            // Prevent lightbox on video clicks
            $(document).on('click', '.fpvg-video-item', function(e) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            });
            
            // Handle video ended
            $(document).on('ended', '.fpvg-gallery-video', function() {
                var $button = $(this).siblings('.fpvg-video-overlay').find('.fpvg-play-button');
                self.showPlayIcon($button);
            });
            
            // Pause other videos when one starts playing
            $(document).on('play', '.fpvg-gallery-video', function() {
                self.pauseOtherVideos(this);
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
        
        integrateWithFlickity: function() {
            var self = this;
            
            // Wait for Flickity to initialize
            setTimeout(function() {
                var $gallery = $('.product-gallery-slider');
                
                if ($gallery.length && $gallery.data('flickity')) {
                    self.flickityInstance = $gallery.data('flickity');
                    
                    // Bind to Flickity events
                    $gallery.on('change.flickity', function() {
                        self.handleSlideChange();
                    });
                    
                    // Adjust video sizes to match images
                    self.adjustVideoSizes();
                }
            }, 500);
        },
        
        handleSlideChange: function() {
            // Pause all videos when slide changes
            this.pauseAllVideos();
        },
        
        pauseAllVideos: function() {
            var self = this;
            
            $('.fpvg-gallery-video').each(function() {
                if (!this.paused) {
                    this.pause();
                    var $button = $(this).siblings('.fpvg-video-overlay').find('.fpvg-play-button');
                    self.showPlayIcon($button);
                }
            });
            
            this.currentVideo = null;
        },
        
        adjustVideoSizes: function() {
            var self = this;
            
            // Get reference image size
            var $refImage = $('.woocommerce-product-gallery__image img').first();
            
            if ($refImage.length) {
                var refWidth = $refImage.width();
                var refHeight = $refImage.height();
                
                $('.fpvg-gallery-video').each(function() {
                    var $video = $(this);
                    var $container = $video.closest('.fpvg-video-container');
                    
                    // Set container size to match reference image
                    $container.css({
                        'width': refWidth + 'px',
                        'height': refHeight + 'px'
                    });
                    
                    // Adjust video to fit container while maintaining aspect ratio
                    self.fitVideoToContainer($video, $container);
                });
            }
        },
        
        fitVideoToContainer: function($video, $container) {
            var containerWidth = $container.width();
            var containerHeight = $container.height();
            
            $video.css({
                'width': '100%',
                'height': '100%',
                'object-fit': 'cover'
            });
        },
        
        setupVideoThumbnails: function() {
            var self = this;
            
            // Create video thumbnails for gallery navigation
            $('.fpvg-video-item').each(function(index) {
                var $videoItem = $(this);
                var $video = $videoItem.find('.fpvg-gallery-video');
                
                if ($video.length) {
                    // Create thumbnail element
                    var $thumbnail = self.createVideoThumbnail($video.get(0), index);
                    
                    // Add to thumbnail gallery
                    $('.product-gallery-thumbnails, .product-gallery-stacked-thumbnails').append($thumbnail);
                }
            });
        },
        
        createVideoThumbnail: function(video, index) {
            var self = this;
            var $thumbnail = $('<div class="fpvg-video-thumbnail woocommerce-product-gallery__image">');
            
            // Create thumbnail video element
            var $thumbVideo = $('<video class="fpvg-thumb-video" muted preload="metadata">');
            $thumbVideo.attr('src', video.src);
            
            // Add play icon overlay
            var $overlay = $('<div class="fpvg-thumb-overlay">');
            var $playIcon = $('<svg class="fpvg-thumb-play-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>');
            $overlay.append($playIcon);
            
            $thumbnail.append($thumbVideo);
            $thumbnail.append($overlay);
            
            // Bind click event
            $thumbnail.on('click', function() {
                self.selectVideoSlide(index);
            });
            
            return $thumbnail;
        },
        
        selectVideoSlide: function(index) {
            if (this.flickityInstance) {
                // Find the video slide index in the main gallery
                var videoSlideIndex = this.findVideoSlideIndex(index);
                
                if (videoSlideIndex !== -1) {
                    this.flickityInstance.select(videoSlideIndex);
                }
            }
        },
        
        findVideoSlideIndex: function(videoIndex) {
            var slideIndex = -1;
            var currentVideoIndex = 0;
            
            $('.woocommerce-product-gallery__wrapper .woocommerce-product-gallery__image').each(function(index) {
                if ($(this).hasClass('fpvg-video-item')) {
                    if (currentVideoIndex === videoIndex) {
                        slideIndex = index;
                        return false; // Break loop
                    }
                    currentVideoIndex++;
                }
            });
            
            return slideIndex;
        },
        
        handleMobileGallery: function() {
            if (!this.isMobile) {
                return;
            }
            
            // Mobile-specific adjustments
            $('.fpvg-video-container').addClass('fpvg-mobile');
            
            // Ensure videos are properly sized on mobile
            this.adjustMobileVideoSizes();
        },
        
        adjustMobileVideoSizes: function() {
            if (!this.isMobile) {
                return;
            }
            
            $('.fpvg-gallery-video').each(function() {
                var $video = $(this);
                var $container = $video.closest('.fpvg-video-container');
                
                $container.css({
                    'width': '100%',
                    'height': 'auto'
                });
                
                $video.css({
                    'width': '100%',
                    'height': 'auto',
                    'object-fit': 'contain'
                });
            });
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

