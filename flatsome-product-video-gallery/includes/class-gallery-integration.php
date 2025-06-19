<?php
/**
 * Gallery Integration Class
 * Handles integration with Flatsome theme's product gallery
 */

if (!defined('ABSPATH')) {
    exit;
}

class FPVG_Gallery_Integration {
    
    public function __construct() {
        // Hook into WooCommerce product gallery
        add_filter('woocommerce_single_product_image_thumbnail_html', array($this, 'add_video_to_gallery'), 10, 2);
        add_action('woocommerce_product_thumbnails', array($this, 'add_video_thumbnails'), 15);
        
        // Hook into Flatsome specific actions
        add_action('wp_enqueue_scripts', array($this, 'enqueue_gallery_scripts'));
        
        // Add video data to product
        add_action('woocommerce_single_product_summary', array($this, 'add_video_data_to_page'), 5);
    }
    
    /**
     * Add video to main gallery
     */
    public function add_video_to_gallery($html, $post_thumbnail_id) {
        global $product;
        
        if (!$product) {
            return $html;
        }
        
        // Get product videos
        $videos = $this->get_product_videos($product->get_id());
        
        if (empty($videos)) {
            return $html;
        }
        
        // Add videos to gallery HTML
        foreach ($videos as $video) {
            $video_html = $this->generate_video_html($video);
            $html .= $video_html;
        }
        
        return $html;
    }
    
    /**
     * Add video thumbnails to gallery
     */
    public function add_video_thumbnails() {
        global $product;
        
        if (!$product) {
            return;
        }
        
        $videos = $this->get_product_videos($product->get_id());
        
        if (empty($videos)) {
            return;
        }
        
        foreach ($videos as $video) {
            echo $this->generate_video_thumbnail_html($video);
        }
    }
    
    /**
     * Get product videos
     */
    private function get_product_videos($product_id) {
        $videos = get_post_meta($product_id, '_product_videos', true);
        
        if (!is_array($videos)) {
            return array();
        }
        
        // Sort by position
        usort($videos, function($a, $b) {
            return intval($a['position']) - intval($b['position']);
        });
        
        return $videos;
    }
    
    /**
     * Generate video HTML for main gallery
     */
    private function generate_video_html($video) {
        $video_id = 'fpvg-video-' . uniqid();
        
        $html = '<div class="woocommerce-product-gallery__image fpvg-video-container" data-video-position="' . esc_attr($video['position']) . '">';
        $html .= '<div class="fpvg-video-wrapper">';
        $html .= '<video id="' . esc_attr($video_id) . '" class="fpvg-product-video" preload="metadata" muted>';
        $html .= '<source src="' . esc_url($video['url']) . '" type="video/' . esc_attr($video['type']) . '">';
        $html .= __('Your browser does not support the video tag.', 'flatsome-product-video-gallery');
        $html .= '</video>';
        $html .= '<div class="fpvg-video-controls">';
        $html .= '<button class="fpvg-play-pause-btn" data-video-id="' . esc_attr($video_id) . '">';
        $html .= '<span class="fpvg-play-icon">▶</span>';
        $html .= '<span class="fpvg-pause-icon" style="display:none;">⏸</span>';
        $html .= '</button>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Generate video thumbnail HTML
     */
    private function generate_video_thumbnail_html($video) {
        $video_id = 'fpvg-thumb-' . uniqid();
        
        $html = '<div class="woocommerce-product-gallery__image fpvg-video-thumbnail" data-video-position="' . esc_attr($video['position']) . '">';
        $html .= '<video class="fpvg-thumbnail-video" preload="metadata" muted>';
        $html .= '<source src="' . esc_url($video['url']) . '" type="video/' . esc_attr($video['type']) . '">';
        $html .= '</video>';
        $html .= '<div class="fpvg-thumbnail-overlay">';
        $html .= '<span class="fpvg-play-icon">▶</span>';
        $html .= '</div>';
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Enqueue gallery scripts
     */
    public function enqueue_gallery_scripts() {
        if (!is_product()) {
            return;
        }
        
        wp_enqueue_script(
            'fpvg-gallery',
            FPVG_PLUGIN_URL . 'assets/js/frontend.js',
            array('jquery'),
            FPVG_VERSION,
            true
        );
        
        wp_enqueue_style(
            'fpvg-gallery',
            FPVG_PLUGIN_URL . 'assets/css/frontend.css',
            array(),
            FPVG_VERSION
        );
        
        // Localize script
        wp_localize_script('fpvg-gallery', 'fpvg_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('fpvg_frontend_nonce')
        ));
    }
    
    /**
     * Add video data to page for JavaScript access
     */
    public function add_video_data_to_page() {
        global $product;
        
        if (!$product) {
            return;
        }
        
        $videos = $this->get_product_videos($product->get_id());
        
        if (empty($videos)) {
            return;
        }
        
        echo '<script type="application/json" id="fpvg-video-data">';
        echo wp_json_encode($videos);
        echo '</script>';
    }
    
    /**
     * Check if current product has videos
     */
    public static function product_has_videos($product_id) {
        $videos = get_post_meta($product_id, '_product_videos', true);
        return !empty($videos) && is_array($videos);
    }
}

