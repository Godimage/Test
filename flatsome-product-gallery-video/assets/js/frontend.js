jQuery(document).ready(function($) {
    // Handle video thumbnail clicks
    $('.fpgv-video-thumbnail a').on('click', function(e) {
        e.preventDefault();
        
        var videoSrc = $(this).closest('.fpgv-video-thumbnail').data('video-src');
        
        if (videoSrc) {
            // Create video modal
            var videoModal = $('<div class="fpgv-video-modal">' +
                             '<div class="fpgv-video-modal-content">' +
                             '<span class="fpgv-video-close">&times;</span>' +
                             '<video controls autoplay>' +
                             '<source src="' + videoSrc + '" type="video/mp4">' +
                             'Your browser does not support the video tag.' +
                             '</video>' +
                             '</div>' +
                             '</div>');
            
            $('body').append(videoModal);
            videoModal.fadeIn();
            
            // Close modal
            $('.fpgv-video-close, .fpgv-video-modal').on('click', function(e) {
                if (e.target === this) {
                    videoModal.fadeOut(function() {
                        $(this).remove();
                    });
                }
            });
        }
    });
    
    // Integration with Flatsome gallery
    if (typeof $.fn.flexslider !== 'undefined') {
        // Handle Flatsome gallery integration
        $('.woocommerce-product-gallery').on('wc-product-gallery-after-init', function() {
            var $gallery = $(this);
            
            // Add video thumbnails to existing gallery
            $('.fpgv-video-thumbnail').each(function() {
                var $videoThumb = $(this);
                var videoSrc = $videoThumb.data('video-src');
                
                if (videoSrc && $gallery.find('.flex-control-thumbs').length) {
                    var thumbHtml = '<li>' +
                                  '<img src="' + $videoThumb.find('img').attr('src') + '" ' +
                                  'data-video-src="' + videoSrc + '" class="fpgv-thumb-video">' +
                                  '<div class="fpgv-thumb-play-icon">▶</div>' +
                                  '</li>';
                    
                    $gallery.find('.flex-control-thumbs').append(thumbHtml);
                }
            });
            
            // Handle thumbnail video clicks
            $gallery.on('click', '.fpgv-thumb-video', function(e) {
                e.preventDefault();
                
                var videoSrc = $(this).data('video-src');
                var $mainImage = $gallery.find('.woocommerce-product-gallery__image img').first();
                
                if (videoSrc && $mainImage.length) {
                    // Replace main image with video
                    var videoHtml = '<video controls autoplay width="' + $mainImage.width() + '">' +
                                  '<source src="' + videoSrc + '" type="video/mp4">' +
                                  '</video>';
                    
                    $mainImage.closest('.woocommerce-product-gallery__image').html(videoHtml);
                }
            });
        });
    }
});
