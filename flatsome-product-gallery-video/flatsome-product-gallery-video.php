<?php
/**
 * Plugin Name: Flatsome Product Gallery Video
 * Description: Add video support to Flatsome WooCommerce product galleries with proper desktop/mobile compatibility
 * Version: 2.0.0
 * Author: Your Name
 * Requires at least: 5.0
 * Tested up to: 6.4
 * WC requires at least: 5.0
 * WC tested up to: 8.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class FlatsomeProductGalleryVideo {
    
    private $video_meta_key = '_product_video_url';
    private $video_thumbnail_meta_key = '_product_video_thumbnail';
    private $auto_resize_meta_key = '_product_video_auto_resize';
    
    public function __construct() {
        add_action('init', array($this, 'init'));
    }
    
    public function init() {
        // Check if WooCommerce and Flatsome are active
        if (!class_exists('WooCommerce') || !defined('FLATSOME_VERSION')) {
            add_action('admin_notices', array($this, 'missing_dependencies_notice'));
            return;
        }
        
        // Admin hooks
        add_action('add_meta_boxes', array($this, 'add_video_meta_box'));
        add_action('save_post', array($this, 'save_video_meta'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        
        // Frontend hooks - Integration with Flatsome gallery system
        add_filter('woocommerce_single_product_image_thumbnail_html', array($this, 'add_video_to_gallery'), 10, 2);
        add_action('wp_enqueue_scripts', array($this, 'frontend_enqueue_scripts'));
        
        // Flatsome specific hooks
        add_filter('flatsome_single_product_thumbnails_render_without_attachments', '__return_true');
        add_action('flatsome_after_product_images', array($this, 'add_video_container'));
        
        // AJAX handlers
        add_action('wp_ajax_get_video_html', array($this, 'ajax_get_video_html'));
        add_action('wp_ajax_nopriv_get_video_html', array($this, 'ajax_get_video_html'));
    }
    
    public function missing_dependencies_notice() {
        echo '<div class="notice notice-error"><p>Flatsome Product Gallery Video requires WooCommerce and Flatsome theme to be active.</p></div>';
    }
    
    /**
     * Add video meta box to product edit page
     */
    public function add_video_meta_box() {
        add_meta_box(
            'product-video-meta-box',
            'Product Video Settings',
            array($this, 'video_meta_box_callback'),
            'product',
            'normal',
            'high'
        );
    }
    
    /**
     * Video meta box HTML
     */
    public function video_meta_box_callback($post) {
        wp_nonce_field('product_video_meta_box', 'product_video_meta_box_nonce');
        
        $video_url = get_post_meta($post->ID, $this->video_meta_key, true);
        $video_thumbnail = get_post_meta($post->ID, $this->video_thumbnail_meta_key, true);
        $auto_resize = get_post_meta($post->ID, $this->auto_resize_meta_key, true);
        
        echo '<table class="form-table">';
        
        // Video URL field
        echo '<tr>';
        echo '<th><label for="product_video_url">Product Video</label></th>';
        echo '<td>';
        echo '<input type="url" id="product_video_url" name="product_video_url" value="' . esc_attr($video_url) . '" style="width: 70%;" placeholder="Enter video URL or upload video file" />';
        echo '<input type="button" id="upload_video_button" class="button" value="Upload Video" style="margin-left: 10px;" />';
        if ($video_url) {
            echo '<input type="button" id="remove_video_button" class="button" value="Remove Video" style="margin-left: 5px; background: #dc3545; color: white;" />';
        }
        echo '<p class="description">Upload a video file or enter a video URL (MP4, WebM, etc.)</p>';
        echo '</td>';
        echo '</tr>';
        
        // Video thumbnail field
        echo '<tr>';
        echo '<th><label for="product_video_thumbnail">Video Thumbnail</label></th>';
        echo '<td>';
        echo '<input type="url" id="product_video_thumbnail" name="product_video_thumbnail" value="' . esc_attr($video_thumbnail) . '" style="width: 70%;" placeholder="Enter thumbnail URL or upload image" />';
        echo '<input type="button" id="upload_thumbnail_button" class="button" value="Upload Thumbnail" style="margin-left: 10px;" />';
        if ($video_thumbnail) {
            echo '<input type="button" id="remove_thumbnail_button" class="button" value="Remove Thumbnail" style="margin-left: 5px; background: #dc3545; color: white;" />';
            echo '<br><img src="' . esc_url($video_thumbnail) . '" style="max-width: 150px; margin-top: 10px;" />';
        }
        echo '<p class="description">Upload or select a thumbnail image for the video</p>';
        echo '</td>';
        echo '</tr>';
        
        // Auto resize checkbox
        echo '<tr>';
        echo '<th><label for="product_video_auto_resize">Auto Resize Video</label></th>';
        echo '<td>';
        echo '<input type="checkbox" id="product_video_auto_resize" name="product_video_auto_resize" value="1" ' . checked($auto_resize, '1', false) . ' />';
        echo '<label for="product_video_auto_resize">Auto resize video to match product gallery image sizes</label>';
        echo '</td>';
        echo '</tr>';
        
        echo '</table>';
    }
    
    /**
     * Save video meta data
     */
    public function save_video_meta($post_id) {
        if (!isset($_POST['product_video_meta_box_nonce']) || 
            !wp_verify_nonce($_POST['product_video_meta_box_nonce'], 'product_video_meta_box') ||
            !current_user_can('edit_post', $post_id) ||
            (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)) {
            return;
        }
        
        // Save video URL
        if (isset($_POST['product_video_url'])) {
            update_post_meta($post_id, $this->video_meta_key, sanitize_url($_POST['product_video_url']));
        }
        
        // Save video thumbnail
        if (isset($_POST['product_video_thumbnail'])) {
            update_post_meta($post_id, $this->video_thumbnail_meta_key, sanitize_url($_POST['product_video_thumbnail']));
        }
        
        // Save auto resize option
        $auto_resize = isset($_POST['product_video_auto_resize']) ? '1' : '0';
        update_post_meta($post_id, $this->auto_resize_meta_key, $auto_resize);
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function admin_enqueue_scripts($hook) {
        if ($hook != 'post.php' && $hook != 'post-new.php') {
            return;
        }
        
        global $post;
        if ($post->post_type != 'product') {
            return;
        }
        
        wp_enqueue_media();
        wp_enqueue_script(
            'product-video-admin',
            plugin_dir_url(__FILE__) . 'assets/admin.js',
            array('jquery'),
            '2.0.0',
            true
        );
    }
    
    /**
     * Enqueue frontend scripts and styles
     */
    public function frontend_enqueue_scripts() {
        if (!is_product()) {
            return;
        }
        
        wp_enqueue_script(
            'flatsome-product-video',
            plugin_dir_url(__FILE__) . 'assets/frontend.js',
            array('jquery'),
            '2.0.0',
            true
        );
        
        wp_enqueue_style(
            'flatsome-product-video',
            plugin_dir_url(__FILE__) . 'assets/frontend.css',
            array(),
            '2.0.0'
        );
        
        wp_localize_script('flatsome-product-video', 'flatsomeVideo', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('flatsome_video_nonce')
        ));
    }
    
    /**
     * Add video to product gallery thumbnails
     * This is the key fix for Flatsome compatibility
     */
    public function add_video_to_gallery($html, $attachment_id) {
        global $product;
        
        if (!$product) {
            return $html;
        }
        
        $video_url = get_post_meta($product->get_id(), $this->video_meta_key, true);
        $video_thumbnail = get_post_meta($product->get_id(), $this->video_thumbnail_meta_key, true);
        
        if (!$video_url) {
            return $html;
        }
        
        // Get gallery image IDs
        $attachment_ids = $product->get_gallery_image_ids();
        
        // Add video thumbnail after the first image (or as first if no images)
        if (empty($attachment_ids) || $attachment_id == $attachment_ids[0]) {
            $video_thumbnail_html = $this->get_video_thumbnail_html($video_url, $video_thumbnail, $product->get_id());
            $html .= $video_thumbnail_html;
        }
        
        return $html;
    }
    
    /**
     * Generate video thumbnail HTML for gallery
     */
    private function get_video_thumbnail_html($video_url, $video_thumbnail, $product_id) {
        $auto_resize = get_post_meta($product_id, $this->auto_resize_meta_key, true);
        $thumbnail_size = wc_get_image_size('gallery_thumbnail');
        
        $thumbnail_src = $video_thumbnail ? $video_thumbnail : $this->get_video_poster($video_url);
        
        $classes = 'product-video-thumbnail';
        if ($auto_resize) {
            $classes .= ' auto-resize';
        }
        
        $style = '';
        if ($auto_resize && $thumbnail_size['width']) {
            $style = sprintf('width: %dpx; height: %dpx; object-fit: cover;', 
                           $thumbnail_size['width'], 
                           $thumbnail_size['height']);
        }
        
        $html = sprintf(
            '<div class="woocommerce-product-gallery__image product-video-thumb-container" data-video-url="%s">
                <a href="#" class="product-video-trigger">
                    <img src="%s" class="%s" alt="Video Thumbnail" style="%s" />
                    <div class="video-play-overlay">
                        <i class="fas fa-play"></i>
                    </div>
                </a>
            </div>',
            esc_url($video_url),
            esc_url($thumbnail_src),
            esc_attr($classes),
            esc_attr($style)
        );
        
        return $html;
    }
    
    /**
     * Add video container for main display
     */
    public function add_video_container() {
        global $product;
        
        if (!$product) {
            return;
        }
        
        $video_url = get_post_meta($product->get_id(), $this->video_meta_key, true);
        
        if (!$video_url) {
            return;
        }
        
        echo '<div id="product-video-container" class="product-video-main-container" style="display: none;">';
        echo $this->get_video_html($video_url, $product->get_id());
        echo '</div>';
    }
    
    /**
     * Generate video HTML
     */
    private function get_video_html($video_url, $product_id) {
        $auto_resize = get_post_meta($product_id, $this->auto_resize_meta_key, true);
        $video_thumbnail = get_post_meta($product_id, $this->video_thumbnail_meta_key, true);
        
        $video_size = wc_get_image_size('woocommerce_single');
        
        $classes = 'product-video-player';
        if ($auto_resize) {
            $classes .= ' auto-resize';
        }
        
        $style = '';
        if ($auto_resize && $video_size['width']) {
            $style = sprintf('width: 100%; max-width: %dpx; height: auto;', $video_size['width']);
        }
        
        $poster_attr = $video_thumbnail ? sprintf('poster="%s"', esc_url($video_thumbnail)) : '';
        
        $html = sprintf(
            '<video class="%s" controls playsinline webkit-playsinline %s style="%s">
                <source src="%s" type="video/mp4">
                <p>Your browser does not support the video tag.</p>
            </video>',
            esc_attr($classes),
            $poster_attr,
            esc_attr($style),
            esc_url($video_url)
        );
        
        return $html;
    }
    
    /**
     * AJAX handler for getting video HTML
     */
    public function ajax_get_video_html() {
        check_ajax_referer('flatsome_video_nonce', 'nonce');
        
        $video_url = sanitize_url($_POST['video_url']);
        $product_id = intval($_POST['product_id']);
        
        if (!$video_url || !$product_id) {
            wp_die();
        }
        
        echo $this->get_video_html($video_url, $product_id);
        wp_die();
    }
    
    /**
     * Get video poster/thumbnail from video URL
     */
    private function get_video_poster($video_url) {
        // Default placeholder or generate from video if possible
        return plugin_dir_url(__FILE__) . 'assets/video-placeholder.png';
    }
}

// Initialize the plugin
new FlatsomeProductGalleryVideo();