<?php
/**
 * Admin Class
 * Handles admin functionality and meta boxes
 */

if (!defined('ABSPATH')) {
    exit;
}

class FPVG_Admin {
    
    public function __construct() {
        add_action('add_meta_boxes', array($this, 'add_product_video_meta_box'));
        add_action('save_post', array($this, 'save_product_video_meta'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        
        // Add settings page
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
    }
    
    /**
     * Add product video meta box
     */
    public function add_product_video_meta_box() {
        add_meta_box(
            'fpvg_product_videos',
            __('Product Videos', 'flatsome-product-video-gallery'),
            array($this, 'render_product_video_meta_box'),
            'product',
            'side',
            'default'
        );
    }
    
    /**
     * Render product video meta box
     */
    public function render_product_video_meta_box($post) {
        // Add nonce field
        wp_nonce_field('fpvg_save_product_videos', 'fpvg_nonce');
        
        // Get existing videos
        $videos = get_post_meta($post->ID, '_product_videos', true);
        if (!is_array($videos)) {
            $videos = array();
        }
        
        ?>
        <div id="fpvg-admin-container">
            <div id="fpvg-video-list">
                <?php if (!empty($videos)): ?>
                    <?php foreach ($videos as $index => $video): ?>
                        <?php echo $this->render_video_item($video, $index); ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <div id="fpvg-upload-section">
                <h4><?php _e('Add New Video', 'flatsome-product-video-gallery'); ?></h4>
                
                <div class="fpvg-upload-area">
                    <input type="file" id="fpvg-video-upload" accept="video/mp4,video/webm,video/ogg" style="display:none;">
                    <button type="button" id="fpvg-upload-btn" class="button">
                        <?php _e('Choose Video File', 'flatsome-product-video-gallery'); ?>
                    </button>
                    <p class="description">
                        <?php printf(
                            __('Maximum file size: %dMB. Allowed formats: %s', 'flatsome-product-video-gallery'),
                            get_option('fpvg_video_max_size', 50),
                            implode(', ', get_option('fpvg_allowed_formats', array('mp4', 'webm', 'ogg')))
                        ); ?>
                    </p>
                </div>
                
                <div id="fpvg-upload-progress" style="display:none;">
                    <div class="fpvg-progress-bar">
                        <div class="fpvg-progress-fill"></div>
                    </div>
                    <p class="fpvg-progress-text">Uploading...</p>
                </div>
            </div>
            
            <input type="hidden" id="fpvg-videos-data" name="fpvg_videos" value="<?php echo esc_attr(wp_json_encode($videos)); ?>">
        </div>
        
        <style>
        #fpvg-admin-container {
            margin-top: 10px;
        }
        
        .fpvg-video-item {
            border: 1px solid #ddd;
            padding: 10px;
            margin-bottom: 10px;
            background: #f9f9f9;
            border-radius: 4px;
        }
        
        .fpvg-video-preview {
            width: 100%;
            max-width: 200px;
            height: auto;
            margin-bottom: 10px;
        }
        
        .fpvg-video-controls {
            display: flex;
            gap: 10px;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .fpvg-position-input {
            width: 60px;
        }
        
        .fpvg-delete-btn {
            background: #dc3232;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 3px;
            cursor: pointer;
        }
        
        .fpvg-upload-area {
            border: 2px dashed #ddd;
            padding: 20px;
            text-align: center;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        
        .fpvg-upload-area.dragover {
            border-color: #0073aa;
            background: #f0f8ff;
        }
        
        .fpvg-progress-bar {
            width: 100%;
            height: 20px;
            background: #f0f0f0;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 10px;
        }
        
        .fpvg-progress-fill {
            height: 100%;
            background: #0073aa;
            width: 0%;
            transition: width 0.3s ease;
        }
        </style>
        <?php
    }
    
    /**
     * Render individual video item
     */
    private function render_video_item($video, $index) {
        ob_start();
        ?>
        <div class="fpvg-video-item" data-index="<?php echo esc_attr($index); ?>">
            <video class="fpvg-video-preview" controls preload="metadata">
                <source src="<?php echo esc_url($video['url']); ?>" type="video/<?php echo esc_attr($video['type']); ?>">
            </video>
            
            <div class="fpvg-video-controls">
                <label>
                    <?php _e('Position:', 'flatsome-product-video-gallery'); ?>
                    <input type="number" class="fpvg-position-input" value="<?php echo esc_attr($video['position']); ?>" min="1" max="99">
                </label>
                
                <button type="button" class="fpvg-delete-btn" data-video-url="<?php echo esc_url($video['url']); ?>">
                    <?php _e('Delete', 'flatsome-product-video-gallery'); ?>
                </button>
            </div>
            
            <div class="fpvg-video-info">
                <small>
                    <?php printf(__('File: %s | Size: %s', 'flatsome-product-video-gallery'), 
                        esc_html($video['filename']), 
                        size_format($video['size'])
                    ); ?>
                </small>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Save product video meta
     */
    public function save_product_video_meta($post_id) {
        // Check if this is an autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // Check post type
        if (get_post_type($post_id) !== 'product') {
            return;
        }
        
        // Check nonce
        if (!isset($_POST['fpvg_nonce']) || !wp_verify_nonce($_POST['fpvg_nonce'], 'fpvg_save_product_videos')) {
            return;
        }
        
        // Check user permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Save videos data
        if (isset($_POST['fpvg_videos'])) {
            $videos_data = json_decode(stripslashes($_POST['fpvg_videos']), true);
            
            if (is_array($videos_data)) {
                // Validate and sanitize video data
                $sanitized_videos = array();
                foreach ($videos_data as $video) {
                    if (isset($video['url']) && isset($video['position'])) {
                        $sanitized_videos[] = array(
                            'url' => esc_url_raw($video['url']),
                            'filename' => sanitize_file_name($video['filename']),
                            'type' => sanitize_text_field($video['type']),
                            'size' => intval($video['size']),
                            'position' => intval($video['position'])
                        );
                    }
                }
                
                update_post_meta($post_id, '_product_videos', $sanitized_videos);
            }
        } else {
            delete_post_meta($post_id, '_product_videos');
        }
    }
    
    /**
     * Enqueue admin scripts
     */
    public function enqueue_admin_scripts($hook) {
        global $post_type;
        
        // Only load on product edit pages
        if (($hook === 'post.php' || $hook === 'post-new.php') && $post_type === 'product') {
            wp_enqueue_script(
                'fpvg-admin',
                FPVG_PLUGIN_URL . 'assets/js/admin.js',
                array('jquery'),
                FPVG_VERSION,
                true
            );
            
            wp_enqueue_style(
                'fpvg-admin',
                FPVG_PLUGIN_URL . 'assets/css/admin.css',
                array(),
                FPVG_VERSION
            );
            
            // Localize script
            wp_localize_script('fpvg-admin', 'fpvg_admin', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'upload_nonce' => wp_create_nonce('fpvg_upload_video'),
                'delete_nonce' => wp_create_nonce('fpvg_delete_video'),
                'max_file_size' => get_option('fpvg_video_max_size', 50) * 1024 * 1024,
                'allowed_types' => get_option('fpvg_allowed_formats', array('mp4', 'webm', 'ogg')),
                'strings' => array(
                    'upload_error' => __('Upload failed. Please try again.', 'flatsome-product-video-gallery'),
                    'delete_confirm' => __('Are you sure you want to delete this video?', 'flatsome-product-video-gallery'),
                    'file_too_large' => __('File is too large.', 'flatsome-product-video-gallery'),
                    'invalid_type' => __('Invalid file type.', 'flatsome-product-video-gallery')
                )
            ));
        }
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_submenu_page(
            'woocommerce',
            __('Product Video Gallery', 'flatsome-product-video-gallery'),
            __('Video Gallery', 'flatsome-product-video-gallery'),
            'manage_woocommerce',
            'fpvg-settings',
            array($this, 'render_settings_page')
        );
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('fpvg_settings', 'fpvg_video_max_size');
        register_setting('fpvg_settings', 'fpvg_allowed_formats');
        register_setting('fpvg_settings', 'fpvg_enable_thumbnails');
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('Product Video Gallery Settings', 'flatsome-product-video-gallery'); ?></h1>
            
            <form method="post" action="options.php">
                <?php settings_fields('fpvg_settings'); ?>
                <?php do_settings_sections('fpvg_settings'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Maximum File Size (MB)', 'flatsome-product-video-gallery'); ?></th>
                        <td>
                            <input type="number" name="fpvg_video_max_size" value="<?php echo esc_attr(get_option('fpvg_video_max_size', 50)); ?>" min="1" max="500">
                            <p class="description"><?php _e('Maximum allowed video file size in megabytes.', 'flatsome-product-video-gallery'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Allowed Video Formats', 'flatsome-product-video-gallery'); ?></th>
                        <td>
                            <?php
                            $allowed_formats = get_option('fpvg_allowed_formats', array('mp4', 'webm', 'ogg'));
                            $all_formats = array('mp4', 'webm', 'ogg', 'avi', 'mov');
                            ?>
                            <?php foreach ($all_formats as $format): ?>
                                <label>
                                    <input type="checkbox" name="fpvg_allowed_formats[]" value="<?php echo esc_attr($format); ?>" <?php checked(in_array($format, $allowed_formats)); ?>>
                                    <?php echo strtoupper($format); ?>
                                </label><br>
                            <?php endforeach; ?>
                            <p class="description"><?php _e('Select which video formats are allowed for upload.', 'flatsome-product-video-gallery'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Enable Video Thumbnails', 'flatsome-product-video-gallery'); ?></th>
                        <td>
                            <input type="checkbox" name="fpvg_enable_thumbnails" value="1" <?php checked(get_option('fpvg_enable_thumbnails', 1)); ?>>
                            <p class="description"><?php _e('Generate and display video thumbnails in the gallery.', 'flatsome-product-video-gallery'); ?></p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}

