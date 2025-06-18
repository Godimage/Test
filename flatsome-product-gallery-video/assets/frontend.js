jQuery(document).ready(function($) {
    'use strict';
    
    var FlatsomeProductVideo = {
        
        init: function() {
            this.bindEvents();
            this.initializeGallery();
        },
        
        bindEvents: function() {
            // Handle video thumbnail clicks
            $(document).on('click', '.product-video-trigger', this.handleVideoThumbnailClick.bind(this));
            
            // Handle regular image thumbnail clicks to hide video
            $(document).on('click', '.woocommerce-product-gallery__image:not(.product-video-thumb-container) a', this.handleImageThumbnailClick.bind(this));
            
            // Handle mobile video playback
            $(document).on('click', '.product-video-player', this.handleVideoClick.bind(this));
            
            // Handle Flatsome gallery changes
            $(document).on('flatsome_gallery_changed', this.handleGalleryChange.bind(this));
        },
        
        handleVideoThumbnailClick: function(e) {
            e.preventDefault();
            
            var $trigger = $(e.currentTarget);
            var $container = $trigger.closest('.product-video-thumb-container');
            var videoUrl = $container.data('video-url');
            
            if (!videoUrl) {
                return;
            }
            
            // Hide main product image
            $('.woocommerce-product-gallery__wrapper .woocommerce-product-gallery__image:first-child').hide();
            
            // Show video container
            this.showVideo(videoUrl);
            
            // Update active thumbnail
            $('.woocommerce-product-gallery__image').removeClass('flex-active');
            $container.addClass('flex-active');
            
            // Prevent lightbox
            return false;
        },
        
        handleImageThumbnailClick: function(e) {
            // Hide video when regular image is clicked
            this.hideVideo();
            
            // Show main product image
            $('.woocommerce-product-gallery__wrapper .woocommerce-product-gallery__image:first-child').show();
        },
        
        handleVideoClick: function(e) {
            // Prevent event propagation to avoid conflicts with Flatsome
            e.stopPropagation();
            
            // For mobile devices, ensure video plays instead of opening lightbox
            if (this.isMobile()) {
                var video = e.currentTarget;
                if (video.paused) {
                    video.play();
                } else {
                    video.pause();
                }
                e.preventDefault();
                return false;
            }
        },
        
        showVideo: function(videoUrl) {
            var $videoContainer = $('#product-video-container');
            
            if ($videoContainer.length === 0) {
                // Create video container if it doesn't exist
                $videoContainer = $('<div id="product-video-container" class="product-video-main-container"></div>');
                $('.woocommerce-product-gallery__wrapper').append($videoContainer);
            }
            
            // Load video HTML via AJAX for dynamic content
            var productId = this.getProductId();
            
            $.ajax({
                url: flatsomeVideo.ajax_url,
                type: 'POST',
                data: {
                    action: 'get_video_html',
                    video_url: videoUrl,
                    product_id: productId,
                    nonce: flatsomeVideo.nonce
                },
                success: function(response) {
                    $videoContainer.html(response).show();
                },
                error: function() {
                    // Fallback HTML
                    var videoHtml = '<video class="product-video-player" controls playsinline webkit-playsinline style="width: 100%; height: auto;">' +
                                   '<source src="' + videoUrl + '" type="video/mp4">' +
                                   '<p>Your browser does not support the video tag.</p>' +
                                   '</video>';
                    $videoContainer.html(videoHtml).show();
                }
            });
        },
        
        hideVideo: function() {
            $('#product-video-container').hide();
        },
        
        initializeGallery: function() {
            // Ensure Flatsome gallery recognizes video thumbnails
            if (typeof window.flatsomeVars !== 'undefined') {
                // Re-initialize Flatsome gallery to include video thumbnails
                setTimeout(function() {
                    if ($('.product-video-thumb-container').length > 0) {
                        // Trigger Flatsome gallery update
                        $(document).trigger('flatsome_gallery_updated');
                    }
                }, 100);
            }
        },
        
        handleGalleryChange: function(e) {
            // Handle Flatsome gallery state changes
            var $activeThumb = $('.woocommerce-product-gallery__image.flex-active');
            
            if ($activeThumb.hasClass('product-video-thumb-container')) {
                var videoUrl = $activeThumb.data('video-url');
                this.showVideo(videoUrl);
            } else {
                this.hideVideo();
            }
        },
        
        getProductId: function() {
            // Try to get product ID from various sources
            if (typeof wc_single_product_params !== 'undefined' && wc_single_product_params.product_id) {
                return wc_single_product_params.product_id;
            }
            
            // Fallback: try to extract from body class
            var bodyClasses = $('body').attr('class');
            var match = bodyClasses.match(/postid-(\d+)/);
            return match ? match[1] : 0;
        },
        
        isMobile: function() {
            return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) || 
                   $(window).width() < 768;
        }
    };
    
    // Initialize when DOM is ready
    FlatsomeProductVideo.init();
    
    // Re-initialize after AJAX events (for compatibility with Flatsome)
    $(document).on('flatsome_ajax_success', function() {
        FlatsomeProductVideo.init();
    });
});