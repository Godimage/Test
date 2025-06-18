<?php
/**
 * Plugin Name: Flatsome Product Gallery Video
 * Description: Add video support to the WooCommerce product gallery with Flatsome theme compatibility.
 * Version: 1.0
 * Author: You
 */

if (!defined('ABSPATH')) exit;

// Enqueue admin media and custom JS
add_action('admin_enqueue_scripts', function($hook){
    if (!in_array($hook, ['post.php', 'post-new.php'])) return;
    wp_enqueue_media();
    wp_enqueue_script('fpgv-admin', plugin_dir_url(__FILE__) . 'admin.js', ['jquery'], '1.0', true);
});

// Enqueue frontend CSS only on single product page
add_action('wp_enqueue_scripts', function(){
    if (!is_product()) return;
    wp_enqueue_style('fpgv-css', plugin_dir_url(__FILE__) . 'style.css');
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

// Add video slide to product gallery
add_filter('woocommerce_single_product_image_thumbnail_html', function($html, $attachment_id){
    static $done = false;
    if ($done) return $html;

    $post_id = get_the_ID();
    $video_id = get_post_meta($post_id, 'fpgv_video_id', true);
    if ($video_id){
        $thumb_id = get_post_meta($post_id, 'fpgv_thumb_id', true);
        $auto = get_post_meta($post_id, 'fpgv_auto_resize', true) === 'yes';
        $video_url = wp_get_attachment_url($video_id);
        $thumb_url = $thumb_id ? wp_get_attachment_url($thumb_id) : '';
        $style = $auto ? 'style="max-width:100%"' : '';

        $video_html = sprintf(
            '<div class="fpgv-video-slide ux-gallery-slide"><video controls %s poster="%s"><source src="%s" type="video/%s"></video></div>',
            $style,
            esc_url($thumb_url),
            esc_url($video_url),
            esc_attr(pathinfo($video_url, PATHINFO_EXTENSION))
        );
        $done = true;
        return $video_html . $html;
    }
    return $html;
}, 10, 2);