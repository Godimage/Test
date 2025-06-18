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
        
        // Add AJAX handlers
        add_action('wp_ajax_fpgv_upload_video', array($this, 'ajax_upload_video'));
        add_action('wp_ajax_fpgv_upload_thumbnail', array($this, 'ajax_upload_thumbnail'));
        add_action('wp_ajax_fpgv_remove_video', array($this, 'ajax_remove_video'));
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
                                <video width="100%" height="150" controls>
                                    <source src="<?php echo esc_url($video_url); ?>" type="video/mp4">
                                </video>
                                <button type="button" class="button fpgv-remove-video"><?php _e('Remove Video', 'fpgv'); ?></button>
                            </div>
                            <div class="fpgv-video-upload-btn" <?php echo $video_url ? 'style="display:none;"' : ''; ?>>
                                <button type="button" class="button fpgv-upload-video"><?php _e('Upload Video', 'fpgv'); ?></button>
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
                                <img src="<?php echo esc_url($thumbnail_url); ?>" style="max-width: 100%; height: auto;">
                                <button type="button" class="button fpgv-remove-thumbnail"><?php _e('Remove Thumbnail', 'fpgv'); ?></button>
                            </div>
                            <div class="fpgv-thumbnail-upload-btn" <?php echo $thumbnail_url ? 'style="display:none;"' : ''; ?>>
                                <button type="button" class="button fpgv-upload-thumbnail"><?php _e('Upload Thumbnail', 'fpgv'); ?></button>
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
        
        if ($hook == 'post.php' && $post_type == 'product') {
            wp_enqueue_media();
            wp_enqueue_script('fpgv-admin', FPGV_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), FPGV_VERSION, true);
            wp_enqueue_style('fpgv-admin', FPGV_PLUGIN_URL . 'assets/css/admin.css', array(), FPGV_VERSION);
            
            wp_localize_script('fpgv-admin', 'fpgv_admin_ajax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('fpgv_admin_nonce')
            ));
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
    
    /**
     * AJAX handler for video upload
     */
    public function ajax_upload_video() {
        check_ajax_referer('fpgv_admin_nonce', 'nonce');
        
        if (!current_user_can('upload_files')) {
            wp_die(__('You do not have permission to upload files.', 'fpgv'));
        }
        
        $attachment_id = intval($_POST['attachment_id']);
        $attachment_url = wp_get_attachment_url($attachment_id);
        
        if ($attachment_url) {
            wp_send_json_success(array(
                'attachment_id' => $attachment_id,
                'attachment_url' => $attachment_url
            ));
        } else {
            wp_send_json_error(__('Failed to get attachment URL.', 'fpgv'));
        }
    }
    
    /**
     * AJAX handler for thumbnail upload
     */
    public function ajax_upload_thumbnail() {
        check_ajax_referer('fpgv_admin_nonce', 'nonce');
        
        if (!current_user_can('upload_files')) {
            wp_die(__('You do not have permission to upload files.', 'fpgv'));
        }
        
        $attachment_id = intval($_POST['attachment_id']);
        $attachment_url = wp_get_attachment_image_url($attachment_id, 'thumbnail');
        
        if ($attachment_url) {
            wp_send_json_success(array(
                'attachment_id' => $attachment_id,
                'attachment_url' => $attachment_url
            ));
        } else {
            wp_send_json_error(__('Failed to get attachment URL.', 'fpgv'));
        }
    }
    
    /**
     * AJAX handler for removing video
     */
    public function ajax_remove_video() {
        check_ajax_referer('fpgv_admin_nonce', 'nonce');
        
        wp_send_json_success();
    }
}

// Initialize the plugin
new FlatsomeProductGalleryVideo();
