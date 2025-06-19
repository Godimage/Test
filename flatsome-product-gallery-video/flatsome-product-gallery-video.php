<?php
/**
 * Plugin Name:       Flatsome Product Gallery Video
 * Plugin URI:        https://example.com/flatsome-product-gallery-video
 * Description:       Adds a video to the Flatsome theme's product gallery using a stable, compatible integration method based on the WooCommerce hooks `woocommerce_product_get_gallery_image_ids` and `woocommerce_single_product_image_thumbnail_html`.
 * Version:           2.0.0
 * Author:            Expert Analyst
 * Author URI:        https://example.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       fvg
 * Domain Path:       /languages
 * WC requires at least: 3.0
 * WC tested up to: 8.0
 */

// Prevent direct file access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main plugin class for Flatsome Product Gallery Video.
 */
final class Flatsome_Product_Gallery_Video {

	/**
	 * Singleton instance.
	 *
	 * @var Flatsome_Product_Gallery_Video
	 */
	private static $instance;

	/**
	 * Returns the singleton instance of the class.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor. Hooks into WordPress actions and filters.
	 */
	private function __construct() {
		// Admin hooks
		add_action( 'add_meta_boxes', array( $this, 'add_video_meta_box' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		add_action( 'save_post_product', array( $this, 'save_video_meta_data' ) );

		// Frontend integration hooks
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
		add_filter( 'woocommerce_product_get_gallery_image_ids', array( $this, 'add_video_thumbnail_to_gallery' ), 20, 2 );
		add_filter( 'woocommerce_single_product_image_thumbnail_html', array( $this, 'add_video_data_to_thumbnail_html' ), 20, 2 );
	}

	/**
	 * Adds the meta box to the product edit screen.
	 */
	public function add_video_meta_box() {
		add_meta_box(
			'fvg_video_meta_box',
			__( 'Product Gallery Video', 'fvg' ),
			array( $this, 'render_video_meta_box' ),
			'product',
			'side',
			'low'
		);
	}

	/**
	 * Renders the content of the video meta box.
	 *
	 * @param WP_Post $post The current post object.
	 */
	public function render_video_meta_box( $post ) {
		// Security nonce
		wp_nonce_field( 'fvg_save_video_meta_data', 'fvg_video_nonce' );

		// Retrieve saved metadata
		$video_id      = get_post_meta( $post->ID, '_fvg_video_id', true );
		$thumbnail_id  = get_post_meta( $post->ID, '_fvg_thumbnail_id', true );
		$auto_resize   = get_post_meta( $post->ID, '_fvg_auto_resize', true );

		$video_file_name = $video_id ? basename(wp_get_attachment_url($video_id)) : '';
        $thumbnail_url = $thumbnail_id ? wp_get_attachment_image_url($thumbnail_id, 'thumbnail') : '';
		?>
		<div id="fvg_meta_box_controls">
            <!-- Video Upload -->
            <p><strong><?php esc_html_e( 'Video File (MP4)', 'fvg' ); ?></strong></p>
            <div id="fvg_video_field_wrapper">
                <span id="fvg_video_file_name"><?php echo esc_html( $video_file_name ); ?></span>
                <input type="hidden" id="fvg_video_id" name="fvg_video_id" value="<?php echo esc_attr( $video_id ); ?>" />
                <button type="button" id="fvg_upload_video_button" class="button" style="<?php echo !empty($video_id) ? 'display:none;' : ''; ?>"><?php esc_html_e( 'Select Video', 'fvg' ); ?></button>
                <button type="button" id="fvg_remove_video_button" class="button" style="<?php echo empty($video_id) ? 'display:none;' : ''; ?>"><?php esc_html_e( 'Remove', 'fvg' ); ?></button>
            </div>

            <!-- Thumbnail Upload -->
            <p style="margin-top:15px;"><strong><?php esc_html_e( 'Video Thumbnail Image', 'fvg' ); ?></strong></p>
            <div id="fvg_thumbnail_field_wrapper">
                <div id="fvg_thumbnail_preview" style="margin-bottom: 5px;">
                    <?php if ( $thumbnail_url ) : ?>
                        <img src="<?php echo esc_url( $thumbnail_url ); ?>" style="max-width:100%; height:auto;" />
                    <?php endif; ?>
                </div>
                <input type="hidden" id="fvg_thumbnail_id" name="fvg_thumbnail_id" value="<?php echo esc_attr( $thumbnail_id ); ?>" />
                <button type="button" id="fvg_upload_thumbnail_button" class="button" style="<?php echo !empty($thumbnail_id) ? 'display:none;' : ''; ?>"><?php esc_html_e( 'Select Image', 'fvg' ); ?></button>
                <button type="button" id="fvg_remove_thumbnail_button" class="button" style="<?php echo empty($thumbnail_id) ? 'display:none;' : ''; ?>"><?php esc_html_e( 'Remove', 'fvg' ); ?></button>
            </div>

            <!-- Auto Resize Checkbox -->
            <p style="margin-top:15px;">
                <label for="fvg_auto_resize">
                    <input type="checkbox" id="fvg_auto_resize" name="fvg_auto_resize" value="1" <?php checked( $auto_resize, '1' ); ?> />
                    <?php esc_html_e( 'Auto Resize Video Lightbox', 'fvg' ); ?>
                </label>
                <span class="description" style="display: block;"><?php esc_html_e( 'Fit video to browser window.', 'fvg' ); ?></span>
            </p>
        </div>
		<?php
	}

	/**
	 * Saves the custom meta data for the product.
	 *
	 * @param int $post_id The ID of the post being saved.
	 */
	public function save_video_meta_data( $post_id ) {
		// Verify nonce.
		if ( ! isset( $_POST['fvg_video_nonce'] ) || ! wp_verify_nonce( $_POST['fvg_video_nonce'], 'fvg_save_video_meta_data' ) ) {
			return;
		}

		// Check user permissions.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Save Video ID.
		update_post_meta( $post_id, '_fvg_video_id', isset( $_POST['fvg_video_id'] ) ? absint( $_POST['fvg_video_id'] ) : '' );

		// Save Thumbnail ID.
		update_post_meta( $post_id, '_fvg_thumbnail_id', isset( $_POST['fvg_thumbnail_id'] ) ? absint( $_POST['fvg_thumbnail_id'] ) : '' );

		// Save Auto Resize option.
		$auto_resize = isset( $_POST['fvg_auto_resize'] ) ? '1' : '0';
		update_post_meta( $post_id, '_fvg_auto_resize', $auto_resize );
	}

	/**
	 * Enqueues scripts and styles for the admin area.
	 *
	 * @param string $hook The current admin page hook.
	 */
	public function enqueue_admin_assets( $hook ) {
		global $post;
		// Only load on the product edit screen.
		if ( ( 'post.php' === $hook || 'post-new.php' === $hook ) && isset( $post->post_type ) && 'product' === $post->post_type ) {
			wp_enqueue_media();
            wp_enqueue_script( 'fvg-admin-script', plugin_dir_url( __FILE__ ) . 'js/admin.js', array( 'jquery', 'wp-mediaelement' ), '2.0.0', true );
		}
	}

	/**
	 * Enqueues scripts and styles for the frontend.
	 */
	public function enqueue_frontend_assets() {
		if ( is_product() ) {
			wp_enqueue_style( 'fvg-style', plugin_dir_url( __FILE__ ) . 'css/style.css', array(), '2.0.0' );
            wp_enqueue_script( 'fvg-script', plugin_dir_url( __FILE__ ) . 'js/frontend.js', array( 'jquery', 'flatsome-magnific-popup' ), '2.0.0', true );
		}
	}

	/**
	 * Adds the video's custom thumbnail to the product gallery image IDs.
	 *
	 * @param array      $image_ids Array of gallery image attachment IDs.
	 * @param WC_Product $product   The product object.
	 * @return array Modified array of image IDs.
	 */
	public function add_video_thumbnail_to_gallery( $image_ids, $product ) {
		if ( ! is_a( $product, 'WC_Product' ) ) {
			return $image_ids;
		}

		$thumbnail_id = get_post_meta( $product->get_id(), '_fvg_thumbnail_id', true );
		$video_id = get_post_meta( $product->get_id(), '_fvg_video_id', true );

		if ( ! empty( $thumbnail_id ) && ! empty( $video_id ) ) {
			// Add the thumbnail to the beginning of the gallery array.
			array_unshift( $image_ids, $thumbnail_id );
		}

		return array_unique( $image_ids );
	}

	/**
	 * Filters the thumbnail HTML to add video data attributes and modify the link.
	 * This revised function is designed to work with the new frontend.js custom lightbox.
	 * It replaces the thumbnail's link with '#' and adds data attributes for the script.
	 *
	 * @param string $html          The thumbnail HTML generated by WooCommerce.
	 * @param int    $attachment_id The attachment ID of the thumbnail being processed.
	 * @return string The modified thumbnail HTML.
	 */
	public function add_video_data_to_thumbnail_html( $html, $attachment_id ) {
		global $product;

		// Ensure we are working with a valid product object.
		if ( ! is_a( $product, 'WC_Product' ) ) {
			return $html;
		}

		$video_thumbnail_id = get_post_meta( $product->get_id(), '_fvg_thumbnail_id', true );

		// 1. IDENTIFY: Check if the current thumbnail is our designated video thumbnail.
		if ( ! empty( $video_thumbnail_id ) && (int) $attachment_id === (int) $video_thumbnail_id ) {

			$video_id = get_post_meta( $product->get_id(), '_fvg_video_id', true );

			// If no video is associated, abort and return the original HTML.
			if ( empty( $video_id ) ) {
				return $html;
			}

			$video_url = wp_get_attachment_url( $video_id );

			// If the video URL can't be resolved, abort.
			if ( ! $video_url ) {
				return $html;
			}

			// 2. PREPARE DATA: Get attributes for the frontend script.
			// The JS expects the string 'true' or 'false', which data() converts to a boolean.
			$auto_resize = get_post_meta( $product->get_id(), '_fvg_auto_resize', true ) ? 'true' : 'false';

			// 3. TRANSFORM HTML: Modify the anchor tag (`<a>`).
			// This is the critical step to integrate with `frontend.js`. We will replace the
			// original `href` attribute with a new set of attributes that includes:
			// - `href="#"`, to prevent the theme's default lightbox action.
			// - `data-fvg-video-url`, providing the video source to our script.
			// - `data-fvg-auto-resize`, passing the resize option to our script.

			$new_attributes = sprintf(
				'href="#" data-fvg-video-url="%s" data-fvg-auto-resize="%s"',
				esc_url( $video_url ),
				esc_attr( $auto_resize )
			);

			// Use a regular expression to robustly find and replace the href attribute.
			// This is safer than simple string replacement as it's not dependent on attribute order.
			$html = preg_replace( '/href="[^"]*"/', $new_attributes, $html, 1 );

			// For good measure, add a specific class to the anchor tag for CSS targeting.
			// This check prevents adding the class multiple times on Ajax refreshes.
			if ( strpos( $html, 'fvg-video-thumbnail' ) === false ) {
				$html = str_replace( 'class="', 'class="fvg-video-thumbnail ', $html );
			}
		}

		return $html;
	}
}

// Instantiate the plugin.
Flatsome_Product_Gallery_Video::get_instance();

