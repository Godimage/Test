<?php
/**
 * Plugin Name: Flatsome Product Video Gallery
 * Plugin URI: https://example.com/flatsome-product-video-gallery
 * Description: Adds video upload functionality to WooCommerce product galleries, specifically designed for Flatsome theme compatibility.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://example.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: flatsome-product-video-gallery
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 * WC tested up to: 9.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('FPVG_VERSION', '1.0.0');
define('FPVG_PLUGIN_FILE', __FILE__);
define('FPVG_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('FPVG_PLUGIN_URL', plugin_dir_url(__FILE__));
define('FPVG_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main plugin class
 */
class Flatsome_Product_Video_Gallery {
    
    /**
     * Single instance of the class
     */
    private static $instance = null;
    
    /**
     * Get single instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        add_action('plugins_loaded', array($this, 'init'));
    }
    
    /**
     * Initialize the plugin
     */
    public function init() {
        // Check if WooCommerce is active
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', array($this, 'woocommerce_missing_notice'));
            return;
        }
        
        // Check if Flatsome theme is active
        if (!$this->is_flatsome_active()) {
            add_action('admin_notices', array($this, 'flatsome_missing_notice'));
        }
        
        // Load plugin files
        $this->load_files();
        
        // Initialize components
        $this->init_hooks();
    }
    
    /**
     * Load required files
     */
    private function load_files() {
        require_once FPVG_PLUGIN_DIR . 'includes/class-admin.php';
        require_once FPVG_PLUGIN_DIR . 'includes/class-frontend.php';
        require_once FPVG_PLUGIN_DIR . 'includes/class-video-handler.php';
        require_once FPVG_PLUGIN_DIR . 'includes/class-gallery-integration.php';
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Initialize admin functionality
        if (is_admin()) {
            new FPVG_Admin();
        }
        
        // Initialize frontend functionality
        if (!is_admin()) {
            new FPVG_Frontend();
        }
        
        // Initialize video handler
        new FPVG_Video_Handler();
        
        // Initialize gallery integration
        new FPVG_Gallery_Integration();
        
        // Plugin activation/deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    /**
     * Check if Flatsome theme is active
     */
    private function is_flatsome_active() {
        $theme = wp_get_theme();
        return $theme->get('Name') === 'Flatsome' || $theme->get('Template') === 'flatsome';
    }
    
    /**
     * WooCommerce missing notice
     */
    public function woocommerce_missing_notice() {
        echo '<div class="notice notice-error"><p>';
        echo __('Flatsome Product Video Gallery requires WooCommerce to be installed and active.', 'flatsome-product-video-gallery');
        echo '</p></div>';
    }
    
    /**
     * Flatsome theme missing notice
     */
    public function flatsome_missing_notice() {
        echo '<div class="notice notice-warning"><p>';
        echo __('Flatsome Product Video Gallery is designed for the Flatsome theme. Some features may not work correctly with other themes.', 'flatsome-product-video-gallery');
        echo '</p></div>';
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Create upload directory if it doesn't exist
        $upload_dir = wp_upload_dir();
        $video_dir = $upload_dir['basedir'] . '/product-videos';
        
        if (!file_exists($video_dir)) {
            wp_mkdir_p($video_dir);
        }
        
        // Set default options
        add_option('fpvg_video_max_size', 50); // 50MB default
        add_option('fpvg_allowed_formats', array('mp4', 'webm', 'ogg'));
        add_option('fpvg_enable_thumbnails', 1);
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Clean up if needed
    }
}

// Initialize the plugin
Flatsome_Product_Video_Gallery::get_instance();

