<?php
/**
 * Plugin Name: Flatsome Product Gallery Video
 * Description: Add video support to Flatsome theme product galleries with UX Builder compatibility
 * Version: 1.0.0
 * Author: Your Name
 * Text Domain: fpgv
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('FPGV_PLUGIN_URL', plugin_dir_url(__FILE__));
define('FPGV_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('FPGV_VERSION', '1.0.0');

// Main plugin class
class FlatsomeProductGalleryVideo {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
    }
    
    public function init() {
        // Hook into WordPress
        add_action('add_meta_boxes', array($this, 'add_video_meta_box'));
        add_action('save_post', array($this, 'save_video_meta'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        
        // Hook into Flatsome gallery system
        add_filter('woocommerce_single_product_image_thumbnail_html', array($this, 'add_video_to_gallery_thumbnails'), 10, 2);
        add_action('woocommerce_product_thumbnails', array($this, 'add_video_to_product_thumbnails'), 25);
    }
    
    /**
     * Add meta box to product edit page
     */
    public function add_video_meta_box() {
        add_meta_box(
            'fpgv_video_meta_box',
            __('Product Gallery Video', 'fpgv'),
            array($this, 'video_meta_box_callback'),
            'product',
            'side',
            'high'
        );
    }
    
    /**
     * Meta box callback function
     */
    public function video_meta_box_callback($post) {
        wp_nonce_field('fpgv_save_video_meta', 'fpgv_video_nonce');
        
        // Get current values
        $video_id = get_post_meta($post->ID, '_fpgv_video_id', true);
        $thumbnail_id = get_post_meta($post->ID, '_fpgv_video_thumbnail_id', true);
        $auto_resize = get_post_meta($post->ID, '_fpgv_auto_resize', true);
        
        $video_url = $video_id ? wp_get_attachment_url($video_id) : '';
        $thumbnail_url = $thumbnail_id ? wp_get_attachment_image_url($thumbnail_id, 'thumbnail') : '';
        
        ?>
        <div id="fpgv-meta-box">
            <table class="form-table">
                <tr>
                    <td>
                        <label for="fpgv_video"><?php _e('Product Video:', 'fpgv'); ?></label>
                        <div class="fpgv-video-upload">
                            <input type="hidden" id="fpgv_video_id" name="fpgv_video_id" value="<?php echo esc_attr($video_id); ?>">
                            <div class="fpgv-video-preview" <?php echo $video_url ? '' : 'style="display:none;"'; ?>>
                                <?php if ($video_url): ?>
                                <video width="100%" height="150" controls>
                                    <source src="<?php echo esc_url($video_url); ?>" type="video/mp4">
                                </video>
                                <?php endif; ?>
                                <button type="button" class="button fpgv-remove-video"><?php _e('Remove Video', 'fpgv'); ?></button>
                            </div>
                            <div class="fpgv-video-upload-btn" <?php echo $video_url ? 'style="display:none;"' : ''; ?>>
                                <button type="button" class="button button-primary fpgv-upload-video"><?php _e('Upload Video', 'fpgv'); ?></button>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="fpgv_thumbnail"><?php _e('Video Thumbnail:', 'fpgv'); ?></label>
                        <div class="fpgv-thumbnail-upload">
                            <input type="hidden" id="fpgv_thumbnail_id" name="fpgv_thumbnail_id" value="<?php echo esc_attr($thumbnail_id); ?>">
                            <div class="fpgv-thumbnail-preview" <?php echo $thumbnail_url ? '' : 'style="display:none;"'; ?>>
                                <?php if ($thumbnail_url): ?>
                                <img src="<?php echo esc_url($thumbnail_url); ?>" style="max-width: 100%; height: auto;">
                                <?php endif; ?>
                                <button type="button" class="button fpgv-remove-thumbnail"><?php _e('Remove Thumbnail', 'fpgv'); ?></button>
                            </div>
                            <div class="fpgv-thumbnail-upload-btn" <?php echo $thumbnail_url ? 'style="display:none;"' : ''; ?>>
                                <button type="button" class="button button-primary fpgv-upload-thumbnail"><?php _e('Upload Thumbnail', 'fpgv'); ?></button>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="fpgv_auto_resize">
                            <input type="checkbox" id="fpgv_auto_resize" name="fpgv_auto_resize" value="1" <?php checked($auto_resize, '1'); ?>>
                            <?php _e('Auto Resize Video', 'fpgv'); ?>
                        </label>
                        <p class="description"><?php _e('Automatically resize video to match product gallery image sizes', 'fpgv'); ?></p>
                    </td>
                </tr>
            </table>
        </div>
        
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            console.log('FPGV Meta Box Script Loaded');
            
            // Check if wp.media exists
            if (typeof wp === 'undefined' || typeof wp.media === 'undefined') {
                console.error('WordPress media library not loaded');
                return;
            }
            
            var fpgv_video_frame;
            var fpgv_thumbnail_frame;
            
            // Video upload
            $(document).on('click', '.fpgv-upload-video', function(e) {
                e.preventDefault();
                console.log('Video upload button clicked');
                
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
                    console.log('Video selected:', attachment);
                    
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
            $(document).on('click', '.fpgv-upload-thumbnail', function(e) {
                e.preventDefault();
                console.log('Thumbnail upload button clicked');
                
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
                    console.log('Thumbnail selected:', attachment);
                    
                    $('#fpgv_thumbnail_id').val(attachment.id);
                    
                    var thumbnailHtml = '<img src="' + attachment.url + '" style="max-width: 100%; height: auto;">' +
                                      '<button type="button" class="button fpgv-remove-thumbnail">Remove Thumbnail</button>';
                    
                    $('.fpgv-thumbnail-preview').html(thumbnailHtml).show();
                    $('.fpgv-thumbnail-upload-btn').hide();
                });
                
                fpgv_thumbnail_frame.open();
            });
            
            // Remove video
            $(document).on('click', '.fpgv-remove-video', function(e) {
                e.preventDefault();
                console.log('Remove video clicked');
                
                $('#fpgv_video_id').val('');
                $('.fpgv-video-preview').hide();
                $('.fpgv-video-upload-btn').show();
            });
            
            // Remove thumbnail
            $(document).on('click', '.fpgv-remove-thumbnail', function(e) {
                e.preventDefault();
                console.log('Remove thumbnail clicked');
                
                $('#fpgv_thumbnail_id').val('');
                $('.fpgv-thumbnail-preview').hide();
                $('.fpgv-thumbnail-upload-btn').show();
            });
        });
        </script>
        <?php
    }
    
    /**
     * Save meta box data
     */
    public function save_video_meta($post_id) {
        // Verify nonce
        if (!isset($_POST['fpgv_video_nonce']) || !wp_verify_nonce($_POST['fpgv_video_nonce'], 'fpgv_save_video_meta')) {
            return;
        }
        
        // Check if user has permission
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Save video ID
        if (isset($_POST['fpgv_video_id'])) {
            update_post_meta($post_id, '_fpgv_video_id', sanitize_text_field($_POST['fpgv_video_id']));
        }
        
        // Save thumbnail ID
        if (isset($_POST['fpgv_thumbnail_id'])) {
            update_post_meta($post_id, '_fpgv_video_thumbnail_id', sanitize_text_field($_POST['fpgv_thumbnail_id']));
        }
        
        // Save auto resize option
        $auto_resize = isset($_POST['fpgv_auto_resize']) ? '1' : '0';
        update_post_meta($post_id, '_fpgv_auto_resize', $auto_resize);
    }
    
    /**
     * Enqueue frontend scripts and styles
     */
    public function enqueue_scripts() {
        if (is_product()) {
            wp_enqueue_script('fpgv-frontend', FPGV_PLUGIN_URL . 'assets/js/frontend.js', array('jquery'), FPGV_VERSION, true);
            wp_enqueue_style('fpgv-frontend', FPGV_PLUGIN_URL . 'assets/css/frontend.css', array(), FPGV_VERSION);
            
            // Localize script for AJAX
            wp_localize_script('fpgv-frontend', 'fpgv_ajax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('fpgv_nonce')
            ));
        }
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function admin_enqueue_scripts($hook) {
        global $post_type;
        
        if (($hook == 'post.php' || $hook == 'post-new.php') && $post_type == 'product') {
            // Enqueue WordPress media scripts
            wp_enqueue_media();
            
            // Enqueue our styles
            wp_enqueue_style('fpgv-admin', FPGV_PLUGIN_URL . 'assets/css/admin.css', array(), FPGV_VERSION);
        }
    }
    
    /**
     * Add video to gallery thumbnails (for stack layout)
     */
    public function add_video_to_gallery_thumbnails($html, $attachment_id) {
        global $product;
        
        if (!$product) return $html;
        
        $video_id = get_post_meta($product->get_id(), '_fpgv_video_id', true);
        $thumbnail_id = get_post_meta($product->get_id(), '_fpgv_video_thumbnail_id', true);
        
        if ($video_id && $thumbnail_id) {
            $video_url = wp_get_attachment_url($video_id);
            $thumbnail_url = wp_get_attachment_image_url($thumbnail_id, 'woocommerce_gallery_thumbnail');
            
            if ($video_url && $thumbnail_url) {
                $video_html = sprintf(
                    '<div class="woocommerce-product-gallery__image fpgv-video-thumbnail" data-video-src="%s">
                        <a href="%s">
                            <img src="%s" class="wp-post-image" alt="%s" title="%s">
                            <div class="fpgv-play-button">
                                <svg width="60" height="60" viewBox="0 0 60 60">
                                    <circle cx="30" cy="30" r="30" fill="rgba(0,0,0,0.7)"/>
                                    <polygon points="24,18 24,42 42,30" fill="white"/>
                                </svg>
                            </div>
                        </a>
                    </div>',
                    esc_url($video_url),
                    esc_url($video_url),
                    esc_url($thumbnail_url),
                    esc_attr__('Product Video', 'fpgv'),
                    esc_attr__('Product Video', 'fpgv')
                );
                
                // Append video after the first image
                if (strpos($html, 'woocommerce-product-gallery__image') !== false) {
                    $html .= $video_html;
                }
            }
        }
        
        return $html;
    }
    
    /**
     * Add video to product thumbnails
     */
    public function add_video_to_product_thumbnails() {
        global $product;
        
        if (!$product) return;
        
        $video_id = get_post_meta($product->get_id(), '_fpgv_video_id', true);
        $thumbnail_id = get_post_meta($product->get_id(), '_fpgv_video_thumbnail_id', true);
        
        if ($video_id && $thumbnail_id) {
            $video_url = wp_get_attachment_url($video_id);
            $thumbnail_url = wp_get_attachment_image_url($thumbnail_id, 'woocommerce_gallery_thumbnail');
            
            if ($video_url && $thumbnail_url) {
                echo sprintf(
                    '<div class="woocommerce-product-gallery__image fpgv-video-thumbnail" data-video-src="%s">
                        <a href="%s">
                            <img src="%s" class="wp-post-image" alt="%s" title="%s">
                            <div class="fpgv-play-button">
                                <svg width="60" height="60" viewBox="0 0 60 60">
                                    <circle cx="30" cy="30" r="30" fill="rgba(0,0,0,0.7)"/>
                                    <polygon points="24,18 24,42 42,30" fill="white"/>
                                </svg>
                            </div>
                        </a>
                    </div>',
                    esc_url($video_url),
                    esc_url($video_url),
                    esc_url($thumbnail_url),
                    esc_attr__('Product Video', 'fpgv'),
                    esc_attr__('Product Video', 'fpgv')
                );
            }
        }
    }
}

// Initialize the plugin
new FlatsomeProductGalleryVideo();
