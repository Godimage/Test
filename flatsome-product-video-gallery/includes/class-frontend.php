<?php
/**
 * Frontend Class
 * Handles frontend functionality and display
 */

if (!defined('ABSPATH')) {
    exit;
}

class FPVG_Frontend {
    
    public function __construct() {
        // Hook into Flatsome theme specific actions
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_scripts'));
        add_action('wp_footer', array($this, 'add_video_gallery_scripts'));
        
        // Modify Flatsome gallery output
        add_filter('flatsome_wc_get_gallery_image_html', array($this, 'modify_gallery_html'), 10, 3);
        
        // Add video data to product page
        add_action('woocommerce_single_product_summary', array($this, 'add_video_data_script'), 5);
    }
    
    /**
     * Enqueue frontend scripts and styles
     */
    public function enqueue_frontend_scripts() {
        if (!is_product()) {
            return;
        }
        
        global $product;
        if (!$product || !FPVG_Gallery_Integration::product_has_videos($product->get_id())) {
            return;
        }
        
        wp_enqueue_script(
            'fpvg-frontend',
            FPVG_PLUGIN_URL . 'assets/js/frontend.js',
            array('jquery'),
            FPVG_VERSION,
            true
        );
        
        wp_enqueue_style(
            'fpvg-frontend',
            FPVG_PLUGIN_URL . 'assets/css/frontend.css',
            array(),
            FPVG_VERSION
        );
        
        // Localize script with video data
        $videos = get_post_meta($product->get_id(), '_product_videos', true);
        if (!is_array($videos)) {
            $videos = array();
        }
        
        wp_localize_script('fpvg-frontend', 'fpvg_data', array(
            'videos' => $videos,
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('fpvg_frontend_nonce'),
            'is_mobile' => wp_is_mobile(),
            'theme_settings' => array(
                'gallery_style' => get_theme_mod('product_image_style', 'default'),
                'gallery_layout' => get_theme_mod('product_layout', 'default')
            )
        ));
    }
    
    /**
     * Modify Flatsome gallery HTML to include videos
     */
    public function modify_gallery_html($html, $attachment_id, $main_image) {
        global $product;
        
        if (!$product) {
            return $html;
        }
        
        // Get product videos
        $videos = get_post_meta($product->get_id(), '_product_videos', true);
        if (empty($videos) || !is_array($videos)) {
            return $html;
        }
        
        // Sort videos by position
        usort($videos, function($a, $b) {
            return intval($a['position']) - intval($b['position']);
        });
        
        // Add videos to gallery
        foreach ($videos as $index => $video) {
            $video_html = $this->generate_video_gallery_item($video, $index);
            $html .= $video_html;
        }
        
        return $html;
    }
    
    /**
     * Generate video gallery item HTML
     */
    private function generate_video_gallery_item($video, $index) {
        $video_id = 'fpvg-video-' . uniqid();
        $poster_url = $this->get_video_poster($video);
        
        $html = '<div class="woocommerce-product-gallery__image fpvg-video-item" data-video-index="' . esc_attr($index) . '" data-video-position="' . esc_attr($video['position']) . '">';
        
        // Video element
        $html .= '<div class="fpvg-video-container">';
        $html .= '<video id="' . esc_attr($video_id) . '" class="fpvg-gallery-video" preload="metadata" muted playsinline';
        
        if ($poster_url) {
            $html .= ' poster="' . esc_url($poster_url) . '"';
        }
        
        $html .= '>';
        $html .= '<source src="' . esc_url($video['url']) . '" type="video/' . esc_attr($video['type']) . '">';
        $html .= __('Your browser does not support the video tag.', 'flatsome-product-video-gallery');
        $html .= '</video>';
        
        // Video controls overlay
        $html .= '<div class="fpvg-video-overlay">';
        $html .= '<button class="fpvg-play-button" data-video-id="' . esc_attr($video_id) . '" aria-label="' . __('Play video', 'flatsome-product-video-gallery') . '">';
        $html .= '<svg class="fpvg-play-icon" viewBox="0 0 24 24" fill="currentColor">';
        $html .= '<path d="M8 5v14l11-7z"/>';
        $html .= '</svg>';
        $html .= '<svg class="fpvg-pause-icon" viewBox="0 0 24 24" fill="currentColor" style="display:none;">';
        $html .= '<path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/>';
        $html .= '</svg>';
        $html .= '</button>';
        $html .= '</div>';
        
        // Video info (hidden, for accessibility)
        $html .= '<span class="fpvg-video-info sr-only">';
        $html .= sprintf(__('Video %d of %d', 'flatsome-product-video-gallery'), $index + 1, count($this->get_all_videos()));
        $html .= '</span>';
        
        $html .= '</div>';
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Get video poster image
     */
    private function get_video_poster($video) {
        // Check if custom poster is set
        if (isset($video['poster']) && !empty($video['poster'])) {
            return $video['poster'];
        }
        
        // Generate poster from video (placeholder for now)
        // In a real implementation, this would extract a frame from the video
        return null;
    }
    
    /**
     * Get all videos for current product
     */
    private function get_all_videos() {
        global $product;
        
        if (!$product) {
            return array();
        }
        
        $videos = get_post_meta($product->get_id(), '_product_videos', true);
        return is_array($videos) ? $videos : array();
    }
    
    /**
     * Add video data script to product page
     */
    public function add_video_data_script() {
        global $product;
        
        if (!$product) {
            return;
        }
        
        $videos = get_post_meta($product->get_id(), '_product_videos', true);
        if (empty($videos) || !is_array($videos)) {
            return;
        }
        
        // Sort videos by position
        usort($videos, function($a, $b) {
            return intval($a['position']) - intval($b['position']);
        });
        
        echo '<script type="application/json" id="fpvg-video-data">';
        echo wp_json_encode($videos);
        echo '</script>';
    }
    
    /**
     * Add video gallery initialization scripts
     */
    public function add_video_gallery_scripts() {
        if (!is_product()) {
            return;
        }
        
        global $product;
        if (!$product || !FPVG_Gallery_Integration::product_has_videos($product->get_id())) {
            return;
        }
        
        ?>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Initialize video gallery integration
            if (typeof FPVG !== 'undefined') {
                FPVG.init();
            }
        });
        </script>
        <?php
    }
    
    /**
     * Check if current theme is Flatsome
     */
    private function is_flatsome_theme() {
        $theme = wp_get_theme();
        return $theme->get('Name') === 'Flatsome' || $theme->get('Template') === 'flatsome';
    }
}

