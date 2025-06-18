jQuery(document).ready(function($) {
    var videoData = window.fpgvVideoData || null;
    var isMobile = false;
    
    // Check if mobile
    function checkMobile() {
        isMobile = $(window).width() <= 768;
        return isMobile;
    }
    
    // Initialize on page load
    setTimeout(function() {
        checkMobile();
        initializeVideoIntegration();
    }, 500);
    
    function initializeVideoIntegration() {
        if (!videoData) return;
        
        if (isMobile) {
            initializeMobileGallery();
        } else {
            initializeDesktopGallery();
        }
        
        // Handle video clicks
        $(document).off('click', '.fpgv-video-slide a[data-video="true"]');
        $(document).on('click', '.fpgv-video-slide a[data-video="true"]', function(e) {
            e.preventDefault();
            e.stopPropagation();
            openVideoLightbox();
        });
        
        // Handle video play/pause
        $(document).off('click', '.fpgv-video-slide video');
        $(document).on('click', '.fpgv-video-slide video', function(e) {
            e.stopPropagation();
            
            if (this.paused) {
                // Pause all other videos first
                $('.woocommerce-product-gallery__wrapper video').not(this).each(function() {
                    this.pause();
                });
                this.play();
            } else {
                this.pause();
            }
        });
    }
    
    function initializeDesktopGallery() {
        // Handle thumbnail clicks for desktop
        $(document).off('click', '.product-gallery-stacked-thumbnails .col a');
        $(document).on('click', '.product-gallery-stacked-thumbnails .col a', function(e) {
            e.preventDefault();
            
            var $clickedThumb = $(this);
            var thumbIndex = $clickedThumb.closest('.col').index();
            var $gallery = $('.woocommerce-product-gallery__wrapper');
            var $allSlides = $gallery.find('.woocommerce-product-gallery__image');
            
            // Hide all slides first
            $allSlides.hide();
            
            // Show the corresponding slide
            var $targetSlide = $allSlides.eq(thumbIndex);
            if ($targetSlide.length) {
                $targetSlide.show();
                
                // Update active thumbnail
                $('.product-gallery-stacked-thumbnails .col').removeClass('active');
                $clickedThumb.closest('.col').addClass('active');
                
                // If this is a video slide, ensure it's properly displayed
                if ($targetSlide.hasClass('fpgv-video-slide')) {
                    var $video = $targetSlide.find('video');
                    if ($video.length) {
                        $video[0].load(); // Reload video to ensure it displays
                    }
                }
            }
        });
        
        // Fix image lightbox for desktop
        fixImageLightbox();
    }
    
    function initializeMobileGallery() {
        var $gallery = $('.woocommerce-product-gallery__wrapper');
        
        // Ensure all slides are visible for mobile slider
        $gallery.find('.woocommerce-product-gallery__image').show();
        
        // Initialize or reinitialize Flickity for mobile
        if (typeof $.fn.flickity !== 'undefined') {
            // Destroy existing instance if it exists
            if ($gallery.data('flickity')) {
                $gallery.flickity('destroy');
            }
            
            // Wait a bit then initialize
            setTimeout(function() {
                $gallery.flickity({
                    cellAlign: 'center',
                    wrapAround: true,
                    autoPlay: false,
                    prevNextButtons: false,
                    adaptiveHeight: true,
                    imagesLoaded: true,
                    lazyLoad: 1,
                    pageDots: false,
                    dragThreshold: 15,
                    rightToLeft: $('body').hasClass('rtl')
                });
                
                // Handle slide change
                $gallery.off('change.flickity');
                $gallery.on('change.flickity', function(event, index) {
                    handleMobileSlideChange(index);
                });
                
                // Ensure first slide is selected
                $gallery.flickity('select', 0);
            }, 200);
        }
        
        // Handle mobile thumbnail clicks
        handleMobileThumbnails();
        
        // Fix mobile lightbox
        fixMobileLightbox();
    }
    
    function handleMobileSlideChange(index) {
        var $gallery = $('.woocommerce-product-gallery__wrapper');
        var $slides = $gallery.find('.woocommerce-product-gallery__image');
        var $currentSlide = $slides.eq(index);
        
        // Pause all videos
        $slides.find('video').each(function() {
            this.pause();
        });
        
        // Update mobile thumbnails if they exist
        var $mobileThumbContainer = $('.product-gallery-thumbnails');
        if ($mobileThumbContainer.length) {
            $mobileThumbContainer.find('.col').removeClass('active');
            var $thumbToActivate = $mobileThumbContainer.find('.col').eq(index);
            if ($thumbToActivate.length) {
                $thumbToActivate.addClass('active');
            }
        }
        
        // If current slide is video, prepare it
        if ($currentSlide.hasClass('fpgv-video-slide')) {
            var $video = $currentSlide.find('video');
            if ($video.length) {
                $video[0].load();
            }
        }
    }
    
    function handleMobileThumbnails() {
        // Handle mobile thumbnail clicks
        $(document).off('click', '.product-gallery-thumbnails .col a');
        $(document).on('click', '.product-gallery-thumbnails .col a', function(e) {
            e.preventDefault();
            
            var $clickedThumb = $(this);
            var thumbIndex = $clickedThumb.closest('.col').index();
            var $gallery = $('.woocommerce-product-gallery__wrapper');
            
            // Use Flickity to go to the slide
            if ($gallery.data('flickity')) {
                $gallery.flickity('select', thumbIndex);
            }
            
            // Update active thumbnail
            $('.product-gallery-thumbnails .col').removeClass('active');
            $clickedThumb.closest('.col').addClass('active');
        });
    }
    
    function fixImageLightbox() {
        // Override default lightbox behavior for images
        $(document).off('click', '.woocommerce-product-gallery__image a:not([data-video="true"])');
        
        $(document).on('click', '.woocommerce-product-gallery__image a:not([data-video="true"])', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            var $clickedImage = $(this);
            var $gallery = $('.woocommerce-product-gallery__wrapper');
            var $allImages = $gallery.find('.woocommerce-product-gallery__image a:not([data-video="true"])');
            var images = [];
            var startIndex = 0;
            
            // Build array of images for lightbox
            $allImages.each(function(index) {
                var $img = $(this).find('img');
                if ($img.length) {
                    var large_image_href = $img.attr('data-large_image') || $(this).attr('href') || $img.attr('src');
                    var title = $img.attr('data-caption') || $img.attr('alt') || '';
                    
                    images.push({
                        src: large_image_href,
                        title: title
                    });
                    
                    if ($(this).is($clickedImage)) {
                        startIndex = index;
                    }
                }
            });
            
            // Open lightbox with correct images
            if (images.length > 0 && typeof $.magnificPopup !== 'undefined') {
                $.magnificPopup.open({
                    items: images,
                    type: 'image',
                    gallery: {
                        enabled: true
                    },
                    mainClass: 'mfp-with-zoom',
                    zoom: {
                        enabled: true,
                        duration: 300,
                        easing: 'ease-in-out',
                        opener: function(openerElement) {
                            return openerElement.is('img') ? openerElement : openerElement.find('img');
                        }
                    }
                }, startIndex);
            }
        });
    }
    
    function fixMobileLightbox() {
        // For mobile, handle lightbox differently
        $(document).off('click', '.woocommerce-product-gallery__image a:not([data-video="true"])');
        
        $(document).on('click', '.woocommerce-product-gallery__image a:not([data-video="true"])', function(e) {
            if (!isMobile) return;
            
            e.preventDefault();
            e.stopPropagation();
            
            var $clickedImage = $(this);
            var $gallery = $('.woocommerce-product-gallery__wrapper');
            var currentIndex = 0;
            
            // Find current slide index
            if ($gallery.data('flickity')) {
                currentIndex = $gallery.data('flickity').selectedIndex;
            }
            
            var $allImages = $gallery.find('.woocommerce-product-gallery__image a:not([data-video="true"])');
            var images = [];
            var startIndex = 0;
            var imageIndex = 0;
            
            // Build array of images for lightbox, accounting for video slides
            $gallery.find('.woocommerce-product-gallery__image').each(function(slideIndex) {
                var $slide = $(this);
                if (!$slide.hasClass('fpgv-video-slide')) {
                    var $img = $slide.find('img');
                    if ($img.length) {
                        var large_image_href = $img.attr('data-large_image') || $slide.find('a').attr('href') || $img.attr('src');
                        var title = $img.attr('data-caption') || $img.attr('alt') || '';
                        
                        images.push({
                            src: large_image_href,
                            title: title
                        });
                        
                        if (slideIndex === currentIndex) {
                            startIndex = imageIndex;
                        }
                        imageIndex++;
                    }
                }
            });
            
            // Open lightbox with correct images
            if (images.length > 0 && typeof $.magnificPopup !== 'undefined') {
                $.magnificPopup.open({
                    items: images,
                    type: 'image',
                    gallery: {
                        enabled: true
                    },
                    mainClass: 'mfp-with-zoom',
                    zoom: {
                        enabled: true,
                        duration: 300,
                        easing: 'ease-in-out'
                    }
                }, startIndex);
            }
        });
    }
    
    function openVideoLightbox() {
        if (!videoData) return;
        
        if (typeof $.magnificPopup !== 'undefined') {
            $.magnificPopup.open({
                items: [{
                    type: 'inline',
                    src: '<div class="fpgv-video-lightbox">' +
                          '<video controls autoplay style="width: 100%; height: auto; max-height: 90vh;" poster="' + videoData.thumbUrl + '" playsinline>' +
                          '<source src="' + videoData.videoUrl + '" type="' + videoData.videoType + '">' +
                          'Your browser does not support the video tag.' +
                          '</video>' +
                          '</div>'
                }],
                type: 'inline',
                mainClass: 'mfp-fade',
                callbacks: {
                    open: function() {
                        // Pause any videos in the main gallery
                        $('.woocommerce-product-gallery__wrapper video').each(function() {
                            this.pause();
                        });
                    },
                    close: function() {
                        // Clean up lightbox content
                        $('.fpgv-video-lightbox').remove();
                    }
                }
            });
        }
    }
    
    // Handle window resize
    $(window).on('resize', function() {
        setTimeout(function() {
            var wasMobile = isMobile;
            checkMobile();
            
            if (wasMobile !== isMobile) {
                // Mode changed, reinitialize
                initializeVideoIntegration();
            }
        }, 100);
    });
    
    // Ensure proper initialization on page load
    $(window).on('load', function() {
        setTimeout(function() {
            checkMobile();
            initializeVideoIntegration();
        }, 1000);
    });
    
    // Handle AJAX updates (for variable products)
    $('body').on('woocommerce_variation_select_change', function() {
        setTimeout(function() {
            checkMobile();
            initializeVideoIntegration();
        }, 500);
    });
    
    // Force mobile gallery initialization if Flatsome hasn't done it
    if (checkMobile()) {
        setTimeout(function() {
            var $gallery = $('.woocommerce-product-gallery__wrapper');
            if ($gallery.length && !$gallery.hasClass('flickity-enabled')) {
                initializeMobileGallery();
            }
        }, 1500);
    }
});

