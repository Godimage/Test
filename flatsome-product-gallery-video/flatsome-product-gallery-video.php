<?php
/**
 * Plugin Name:       Flatsome Product Gallery Video
 * Plugin URI:        https://github.com/expert-analyst/flatsome-product-gallery-video
 * Description:       Adds a video to the WooCommerce product gallery for the Flatsome theme. This version fixes meta box placement, media uploader functionality, and frontend display.
 * Version:           5.0.0
 * Author:            Expert Analyst
 * Author URI:        https://example.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       flatsome-product-gallery-video
 * Domain Path:       /languages
 * WC requires at least: 3.0.0
 * WC tested up to: 8.9
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'FVG_VERSION', '5.0.0' );
define( 'FVG_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'FVG_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

// =========================================================================
// 1. ADMIN AREA SETUP
// =========================================================================

/**
 * Registers the meta box on the product edit screen.
 *
 * Ensures the meta box appears in the side context for correct placement.
 */
function fvg_add_video_meta_box() {
	add_meta_box(
		'fvg_product_video_meta_box',
		__( 'Product Gallery Video', 'flatsome-product-gallery-video' ),
		'fvg_video_meta_box_html',
		'product',
		'side', // Fix: Places the meta box in the sidebar.
		'default'
	);
}
add_action( 'add_meta_boxes', 'fvg_add_video_meta_box' );

/**
 * Renders the HTML content for the video meta box.
 *
 * Provides a user-friendly interface with media uploaders for video and thumbnail.
 *
 * @param WP_Post $post The current post object.
 */
function fvg_video_meta_box_html( $post ) {
	// Security nonce.
	wp_nonce_field( 'fvg_save_product_video_meta_action', 'fvg_video_meta_nonce' );

	// Get saved values.
	$video_id     = get_post_meta( $post->ID, '_fvg_video_id', true );
	$thumbnail_id = get_post_meta( $post->ID, '_fvg_thumbnail_id', true );
	$auto_resize  = get_post_meta( $post->ID, '_fvg_auto_resize', true );

	$video_filename      = $video_id ? basename( wp_get_attachment_url( $video_id ) ) : '';
	$thumbnail_image_src = $thumbnail_id ? wp_get_attachment_image_url( $thumbnail_id, 'thumbnail' ) : '';
	?>
	<div class="fvg-meta-box-container">
		<!-- Video Uploader Section -->
		<div class="fvg-field-group">
			<input type="hidden" id="_fvg_video_id" name="_fvg_video_id" value="<?php echo esc_attr( $video_id ); ?>">
			<div id="fvg-video-preview" class="fvg-preview-container">
				<?php if ( $video_id ) : ?>
					<span class="fvg-file-name"><?php echo esc_html( $video_filename ); ?></span>
				<?php else : ?>
					<span class="fvg-placeholder"><?php _e( 'No video selected', 'flatsome-product-gallery-video' ); ?></span>
				<?php endif; ?>
			</div>
			<div class="fvg-button-wrapper">
				<button type="button" class="button" id="fvg_upload_video_button"><?php _e( 'Upload Video', 'flatsome-product-gallery-video' ); ?></button>
				<button type="button" class="button-link-delete" id="fvg_remove_video_button" style="<?php echo $video_id ? '' : 'display:none;'; ?>"><?php _e( 'Remove', 'flatsome-product-gallery-video' ); ?></button>
			</div>
		</div>

		<!-- Thumbnail Uploader Section -->
		<div class="fvg-field-group">
			<input type="hidden" id="_fvg_thumbnail_id" name="_fvg_thumbnail_id" value="<?php echo esc_attr( $thumbnail_id ); ?>">
			<div id="fvg-thumbnail-preview" class="fvg-preview-container">
				<?php if ( $thumbnail_id ) : ?>
					<img src="<?php echo esc_url( $thumbnail_image_src ); ?>" alt="<?php _e( 'Thumbnail Preview', 'flatsome-product-gallery-video' ); ?>">
				<?php else : ?>
					<span class="fvg-placeholder"><?php _e( 'No thumbnail selected', 'flatsome-product-gallery-video' ); ?></span>
				<?php endif; ?>
			</div>
			<p class="description"><?php _e( 'This image appears in the gallery thumbnails.', 'flatsome-product-gallery-video' ); ?></p>
			<div class="fvg-button-wrapper">
				<button type="button" class="button" id="fvg_upload_thumbnail_button"><?php _e( 'Upload Thumbnail', 'flatsome-product-gallery-video' ); ?></button>
				<button type="button" class="button-link-delete" id="fvg_remove_thumbnail_button" style="<?php echo $thumbnail_id ? '' : 'display:none;'; ?>"><?php _e( 'Remove', 'flatsome-product-gallery-video' ); ?></button>
			</div>
		</div>

		<!-- Auto Resize Checkbox -->
		<div class="fvg-field-group">
			<label for="_fvg_auto_resize">
				<input type="checkbox" id="_fvg_auto_resize" name="_fvg_auto_resize" value="yes" <?php checked( $auto_resize, 'yes' ); ?>>
				<?php _e( 'Auto-resize video (cover area)', 'flatsome-product-gallery-video' ); ?>
			</label>
		</div>
	</div>
	<?php
}

/**
 * Enqueues scripts and styles for the admin area.
 *
 * This function loads the WordPress media uploader scripts and the plugin's
 * admin script only on product edit pages.
 *
 * @param string $hook The current admin page hook.
 */
function fvg_enqueue_admin_scripts( $hook ) {
	global $post;
	if ( ( 'post.php' === $hook || 'post-new.php' === $hook ) && isset( $post->post_type ) && 'product' === $post->post_type ) {
		// Enqueue WordPress media scripts.
		wp_enqueue_media();
		// Enqueue the plugin's admin script to handle the media uploader logic.
		wp_enqueue_script(
			'fvg-admin-script',
			FVG_PLUGIN_URL . 'js/admin.js',
			array( 'jquery', 'wp-mediaelement' ),
			FVG_VERSION,
			true
		);
	}
}
add_action( 'admin_enqueue_scripts', 'fvg_enqueue_admin_scripts' );


/**
 * Saves the product video meta data.
 *
 * Triggered when a product is saved. Performs security checks and sanitizes data.
 *
 * @param int $post_id The ID of the post being saved.
 */
function fvg_save_product_video_meta( $post_id ) {
	// Verify nonce.
	if ( ! isset( $_POST['fvg_video_meta_nonce'] ) || ! wp_verify_nonce( $_POST['fvg_video_meta_nonce'], 'fvg_save_product_video_meta_action' ) ) {
		return;
	}
	// Check for autosave.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	// Check user permissions.
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	// Only save for 'product' post type.
	if ( 'product' !== get_post_type( $post_id ) ) {
		return;
	}

	// Save Video ID.
	$video_id = isset( $_POST['_fvg_video_id'] ) ? absint( $_POST['_fvg_video_id'] ) : '';
	update_post_meta( $post_id, '_fvg_video_id', $video_id );

	// Save Thumbnail ID.
	$thumbnail_id = isset( $_POST['_fvg_thumbnail_id'] ) ? absint( $_POST['_fvg_thumbnail_id'] ) : '';
	update_post_meta( $post_id, '_fvg_thumbnail_id', $thumbnail_id );

	// Save Auto-Resize Checkbox.
	$auto_resize = isset( $_POST['_fvg_auto_resize'] ) && 'yes' === $_POST['_fvg_auto_resize'] ? 'yes' : 'no';
	update_post_meta( $post_id, '_fvg_auto_resize', $auto_resize );
}
add_action( 'save_post', 'fvg_save_product_video_meta' );


// =========================================================================
// 2. FRONTEND INTEGRATION
// =========================================================================

/**
 * Enqueues frontend scripts and styles.
 *
 * Loads assets only on single product pages that have a video configured,
 * ensuring optimal performance.
 */
function fvg_enqueue_frontend_scripts() {
	// Crucial check to prevent errors on non-product pages.
	if ( ! is_product() ) {
		return;
	}

	global $product;
	if ( ! is_a( $product, 'WC_Product' ) ) {
		return;
	}

	// Only load assets if a video is actually set for this product.
	if ( get_post_meta( $product->get_id(), '_fvg_video_id', true ) ) {
		wp_enqueue_style( 'fvg-style', FVG_PLUGIN_URL . 'css/style.css', array(), FVG_VERSION );
		wp_enqueue_script( 'fvg-frontend-script', FVG_PLUGIN_URL . 'js/frontend.js', array( 'jquery' ), FVG_VERSION, true );
	}
}
add_action( 'wp_enqueue_scripts', 'fvg_enqueue_frontend_scripts' );

/**
 * Adds the video thumbnail ID to the product gallery image array.
 *
 * This ensures the thumbnail is displayed alongside other gallery images.
 *
 * @param array $image_ids Array of gallery attachment IDs.
 * @return array Modified array with the video thumbnail ID.
 */
function fvg_add_thumbnail_to_gallery( $image_ids ) {
	global $product;
	if ( ! is_a( $product, 'WC_Product' ) ) {
		return $image_ids;
	}

	$thumbnail_id = get_post_meta( $product->get_id(), '_fvg_thumbnail_id', true );

	if ( $thumbnail_id && ! in_array( $thumbnail_id, $image_ids ) ) {
		// Add the thumbnail to the beginning of the gallery.
		array_unshift( $image_ids, $thumbnail_id );
	}

	return $image_ids;
}
add_filter( 'woocommerce_product_get_gallery_image_ids', 'fvg_add_thumbnail_to_gallery', 10, 1 );

/**
 * Modifies the HTML for the video thumbnail in the gallery.
 *
 * This is the core function for the frontend integration. It identifies the
 * specific video thumbnail and replaces its standard anchor with a custom one
 * containing the necessary data attributes for the JavaScript handler.
 *
 * @param string $html          The original thumbnail HTML (including <li> wrapper).
 * @param int    $attachment_id The ID of the current thumbnail's attachment.
 * @return string The modified or original HTML.
 */
function fvg_modify_thumbnail_html( $html, $attachment_id ) {
	global $product;
	if ( ! is_a( $product, 'WC_Product' ) ) {
		return $html;
	}

	$product_id         = $product->get_id();
	$video_thumbnail_id = get_post_meta( $product_id, '_fvg_thumbnail_id', true );

	// Only proceed if the current thumbnail is the one designated for the video.
	if ( ! $video_thumbnail_id || (int) $attachment_id !== (int) $video_thumbnail_id ) {
		return $html;
	}

	$video_id = get_post_meta( $product_id, '_fvg_video_id', true );
	if ( ! $video_id ) {
		return $html;
	}

	$video_url = wp_get_attachment_url( $video_id );
	if ( ! $video_url ) {
		return $html;
	}

	// Prepare data attributes.
	$auto_resize_val = get_post_meta( $product_id, '_fvg_auto_resize', true ) === 'yes' ? 'yes' : 'no';
	$data_attrs      = sprintf(
		'href="javascript:void(0);" class="fvg-video-link" data-fvg-video-url="%s" data-fvg-auto-resize="%s"',
		esc_url( $video_url ),
		esc_attr( $auto_resize_val )
	);

	// Add a target class to the parent <li> for reliable CSS and JS targeting.
	$html = str_replace( '<li class="', '<li class="flatsome-video-gallery-item ', $html );

	// Replace the original anchor tag's attributes with our custom ones.
	$html = preg_replace( '/<a\s+href="[^"]*"/i', '<a ' . $data_attrs, $html );

	return $html;
}
add_filter( 'woocommerce_single_product_image_thumbnail_html', 'fvg_modify_thumbnail_html', 20, 2 );

