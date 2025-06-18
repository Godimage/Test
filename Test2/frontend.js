jQuery(document).ready(function($) {
    var videoData = window.fpgvVideoData || null;
    var isMobile = false;

    // Check if the device is mobile
    function checkMobile() {
        isMobile = $(window).width() <= 768;
        return isMobile;
    }

    // Initialize video integration on page load
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
            openVideoLightbox();
        });

        // Handle video play/pause
        $(document).off('click', '.fpgv-video-slide video');
        $(document).on('click', '.fpgv-video-slide video', function(e) {
            e.stopPropagation();
            this.paused ? this.play() : this.pause();
        });
    }

    // Initialize gallery for desktop
    function initializeDesktopGallery() {
        // Reinitialize UXSlider and UXLightbox
        $('.product-gallery').each(function() {
            var slider = $(this).data('xuSlider');
            if (slider) slider.destroy();
        });
        UXSlider.init($('.product-gallery'));
        UXLightbox.init($('.product-gallery'));
    }

    // Initialize gallery for mobile
    function initializeMobileGallery() {
        var $gallery = $('.woocommerce-product-gallery__wrapper');

        // Ensure all slides are visible for mobile slider
        $gallery.find('.woocommerce-product-gallery__image').show();

        // Initialize Flickity for mobile
        if (typeof $.fn.flickity !== 'undefined') {
            if ($gallery.data('flickity')) {
                $gallery.flickity('destroy');
            }

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
    }

    // Handle mobile slide change
    function handleMobileSlideChange(index) {
        var $gallery = $('.woocommerce-product-gallery__wrapper');
        var $slides = $gallery.find('.woocommerce-product-gallery__image');
        var $currentSlide = $slides.eq(index);

        // Pause all videos
        $slides.find('video').each(function() {
            this.pause();
        });

        // Update mobile thumbnails
        var $mobileThumbContainer = $('.product-gallery-thumbnails');
        if ($mobileThumbContainer.length) {
            $mobileThumbContainer.find('.col').removeClass('active');
            var $thumbToActivate = $mobileThumbContainer.find('.col').eq(index);
            if ($thumbToActivate.length) {
                $thumbToActivate.addClass('active');
            }
        }

        // Prepare video if current slide is video
        if ($currentSlide.hasClass('fpgv-video-slide')) {
            var $video = $currentSlide.find('video');
            if ($video.length) {
                $video[0].load();
            }
        }
    }

    // Handle mobile thumbnail clicks
    function handleMobileThumbnails() {
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

    // Open video in lightbox
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