<?php
/**
 * Plugin Name:       Flatsome Product Video Gallery
 * Description:       Adds a video upload feature to the WooCommerce product gallery for the Flatsome theme.
 * Version:           1.1
 * Author:            Neo
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       flatsome-product-video-gallery
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * ========================================================================
 *  Plugin File Structure
 * ========================================================================
 *
 * flatsome-product-video-gallery/
 * |
 * |- flatsome-product-video-gallery.php (This file)
 * |
 * |- includes/
 * |  |- class-fpvg-admin.php       (Handles admin-side functionality)
 * |  |- class-fpvg-frontend.php    (Handles front-end display logic)
 * |
 * |- admin/
 * |  |- css/
 * |  |  |- fpvg-admin.css          (Styles for the admin metabox)
 * |  |
 * |  |- js/
 * |     |- fpvg-admin.js           (JavaScript for the admin media uploader)
 * |
 * |- public/
 *    |- css/
 *    |  |- fpvg-frontend.css       (Styles for the video on the product page)
 *    |
 *    |- js/
 *       |- fpvg-frontend.js        (JavaScript for integrating the video)
 *
 */

/**
 * Load plugin classes.
 */
require_once plugin_dir_path( __FILE__ ) . 'includes/class-fpvg-admin.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-fpvg-frontend.php';


/**
 * Begins execution of the plugin.
 *
 * Creates the container function for the plugin's classes and
 * fires the main action hook.
 *
 * @since    1.0
 */
function run_flatsome_product_video_gallery() {

	// Instantiate the classes
	$admin    = new FPVG_Admin();
	$frontend = new FPVG_Frontend();

}

// Hook to run the plugin initializer function
add_action( 'plugins_loaded', 'run_flatsome_product_video_gallery' );
