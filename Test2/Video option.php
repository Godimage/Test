<?php
/**
 * Plugin Name: Flatsome Product Gallery Video
 * Description: Add video support to the WooCommerce product gallery with Flatsome theme compatibility.
 * Version: 1.1
 * Author: You
 */

if (!defined('ABSPATH')) exit;

// Enqueue admin media and custom JS
add_action('admin_enqueue_scripts', function($hook){
    if (!in_array($hook, ['post.php', 'post-new.php'])) return;
    wp_enqueue_media();
    wp_enqueue_script('fpgv-admin', plugin_dir_url(__FILE__) . 'admin.js', ['jquery'], '1.1', true);
});

// Enqueue frontend CSS and JS only on single product page
add_action('wp_enqueue_scripts', function(){
    if (!is_product()) return;
    wp_enqueue_style('fpgv-css', plugin_dir_url(__FILE__) . 'style.css');
    wp_enqueue_script('fpgv-frontend', plugin_dir_url(__FILE__) . 'frontend.js', ['jquery'], '1.1', true);
});

// Add custom meta fields in product edit page
add_action('woocommerce_product_options_general_product_data', function(){
    global $post;

    woocommerce_wp_hidden_input(['id' => 'fpgv_video_id']);
    echo '<p class="form-field fpgv_video">
        <label>Product Video</label>
        <button type="button" class="button" id="fpgv_upload_video">Select Video</button>
        <span id="fpgv_video_filename"></span>
        <button type="button" class="button" id="fpgv_remove_video">Remove</button>
    </p>';

    woocommerce_wp_hidden_input(['id' => 'fpgv_thumb_id']);
    echo '<p class="form-field fpgv_thumb">
        <label>Video Thumbnail</label>
        <button type="button" class="button" id="fpgv_upload_thumb">Select Thumbnail</button>
        <img id="fpgv_thumb_preview" style="max-width:100px;display:block;margin-top:5px;">
        <button type="button" class="button" id="fpgv_remove_thumb">Remove</button>
    </p>';

    woocommerce_wp_checkbox([
        'id' => 'fpgv_auto_resize',
        'label' => 'Auto Resize Video',
    ]);
});

// Save meta values
add_action('woocommerce_process_product_meta', function($post_id){
    update_post_meta($post_id, 'fpgv_video_id', intval($_POST['fpgv_video_id'] ?? 0));
    update_post_meta($post_id, 'fpgv_thumb_id', intval($_POST['fpgv_thumb_id'] ?? 0));
    update_post_meta($post_id, 'fpgv_auto_resize', isset($_POST['fpgv_auto_resize']) ? 'yes' : 'no');
});

// Add video to product gallery by modifying the gallery attachment IDs
add_filter('woocommerce_product_get_gallery_image_ids', function($attachment_ids, $product) {
    $video_id = get_post_meta($product->get_id(), 'fpgv_video_id', true);

    if ($video_id) {
        // Add a special marker for the video position
        $attachment_ids[] = 'video_' . $video_id;
    }

    return $attachment_ids;
}, 10, 2);

// Modify the gallery image HTML to include video
add_filter('woocommerce_single_product_image_thumbnail_html', function($html, $attachment_id) {
    // Check if this is our video marker
    if (is_string($attachment_id) && strpos($attachment_id, 'video_') === 0) {
        $video_id = str_replace('video_', '', $attachment_id);
        $post_id = get_the_ID();
        $thumb_id = get_post_meta($post_id, 'fpgv_thumb_id', true);
        $auto_resize = get_post_meta($post_id, 'fpgv_auto_resize', true) === 'yes';
        $video_url = wp_get_attachment_url($video_id);
        $thumb_url = $thumb_id ? wp_get_attachment_url($thumb_id) : '';
        $video_type = wp_check_filetype($video_url)['type'];

        // Create video slide that matches WooCommerce gallery structure
        $html = sprintf(
            '<div data-thumb="%s" data-thumb-alt="Video" class="woocommerce-product-gallery__image fpgv-video-slide">
                <a href="%s" data-video-url="%s" data-video-type="%s" data-video="true">
                    <video controls %s poster="%s" preload="metadata" style="width:100%%; height:auto;" playsinline>
                        <source src="%s" type="%s">
                        Your browser does not support the video tag.
                    </video>
                </a>
            </div>',
            esc_url($thumb_url),
            esc_url($video_url),
            esc_url($video_url),
            esc_attr($video_type),
            $auto_resize ? 'style="max-width:100%; height:auto;"' : '',
            esc_url($thumb_url),
            esc_url($video_url),
            esc_attr($video_type)
        );

        return $html;
    }

    return $html;
}, 10, 2);

// Inject video data for frontend JavaScript
add_action('wp_footer', function(){
    if (!is_product()) return;

    $post_id = get_the_ID();
    $video_id = get_post_meta($post_id, 'fpgv_video_id', true);

    if ($video_id) {
        $thumb_id = get_post_meta($post_id, 'fpgv_thumb_id', true);
        $video_url = wp_get_attachment_url($video_id);
        $thumb_url = $thumb_id ? wp_get_attachment_url($thumb_id) : '';
        $video_type = wp_check_filetype($video_url)['type'];
        ?>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Add video data to gallery
            if (typeof window.fpgvVideoData === 'undefined') {
                window.fpgvVideoData = {
                    videoUrl: '<?php echo esc_js($video_url); ?>',
                    thumbUrl: '<?php echo esc_js($thumb_url); ?>',
                    videoType: '<?php echo esc_js($video_type); ?>'
                };
            }
        });
        </script>
        <?php
    }
});

// Correct the template override path for Flatsome
add_filter('flatsome_override_templates', function($paths) {
    $paths['single-product/product-image.php'] = plugin_dir_path(__FILE__) . 'overrides/woocommerce/single-product/product-image.php';
    return $paths;
});
