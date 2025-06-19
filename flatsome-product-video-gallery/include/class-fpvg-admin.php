<?php
/**
 * Handles all admin-side functionality for the Flatsome Product Video Gallery plugin.
 *
 * This class is responsible for creating the meta box on the product edit screen,
 * handling the saving of video data, and enqueuing necessary admin scripts and styles.
 *
 * @package    Flatsome_Product_Video_Gallery
 * @subpackage Flatsome_Product_Video_Gallery/includes
 * @author     Neo
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FPVG_Admin {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'save_post_product', array( $this, 'save_meta_box_data' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Register the meta box for the product edit screen.
	 *
	 * @since 1.0
	 */
	public function add_meta_box() {
		add_meta_box(
			'fpvg_video_meta_box',
			__( 'Product Gallery Video', 'flatsome-product-video-gallery' ),
			array( $this, 'render_meta_box' ),
			'product',
			'side',
			'default'
		);
	}

	/**
	 * Render the content of the meta box.
	 *
	 * This method displays the HTML fields for uploading a video, setting its
	 * position in the gallery, and previewing the selected video.
	 *
	 * @since 1.0
	 * @param WP_Post $post The post object.
	 */
	public function render_meta_box( $post ) {
		// Add a nonce field for security.
		wp_nonce_field( 'fpvg_save_meta_box_data', 'fpvg_meta_box_nonce' );

		// Retrieve existing values from the database.
		$video_id       = get_post_meta( $post->ID, '_fpvg_video_id', true );
		$video_url      = $video_id ? wp_get_attachment_url( $video_id ) : '';
		$video_position = get_post_meta( $post->ID, '_fpvg_video_position', true );

		// Set a default position if none is saved.
		if ( empty( $video_position ) ) {
			$video_position = 1;
		}

		?>
		<div id="fpvg-meta-box-container">
			<!-- Hidden input to store the video attachment ID -->
			<input type="hidden" id="fpvg_video_id" name="fpvg_video_id" value="<?php echo esc_attr( $video_id ); ?>" />

			<!-- Video preview area -->
			<div class="fpvg-video-preview-wrapper">
				<?php if ( $video_url ) : ?>
					<video src="<?php echo esc_url( $video_url ); ?>" controls style="width:100%; height:auto;"></video>
				<?php endif; ?>
			</div>

			<!-- Action buttons -->
			<div class="fpvg-buttons">
				<button type="button" id="fpvg_upload_video_button" class="button"><?php _e( 'Upload/Set Video', 'flatsome-product-video-gallery' ); ?></button>
				<button type="button" id="fpvg_remove_video_button" class="button button-secondary" style="<?php echo $video_id ? '' : 'display:none;'; ?>"><?php _e( 'Remove Video', 'flatsome-product-video-gallery' ); ?></button>
			</div>

			<!-- Video position setting -->
			<div class="fpvg-position-setting">
				<p>
					<label for="fpvg_video_position"><?php _e( 'Video Position in Gallery', 'flatsome-product-video-gallery' ); ?></label>
					<input type="number" id="fpvg_video_position" name="fpvg_video_position" value="<?php echo esc_attr( $video_position ); ?>" min="1" step="1" style="width:100%;" />
					<small><?php _e( 'Enter the position where the video should appear (e.g., 1 for the first spot).', 'flatsome-product-video-gallery' ); ?></small>
				</p>
			</div>
		</div>
		<?php
	}

	/**
	 * Save the meta box data when a product is saved.
	 *
	 * This method performs security checks (nonce, user permissions) before
	 * sanitizing and saving the video ID and position to post meta.
	 *
	 * @since 1.0
	 * @param int $post_id The ID of the post being saved.
	 */
	public function save_meta_box_data( $post_id ) {
		// 1. Check if our nonce is set.
		if ( ! isset( $_POST['fpvg_meta_box_nonce'] ) ) {
			return;
		}

		// 2. Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $_POST['fpvg_meta_box_nonce'], 'fpvg_save_meta_box_data' ) ) {
			return;
		}

		// 3. If this is an autosave, our form has not been submitted, so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// 4. Check the user's permissions.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Sanitize and save the video ID.
		if ( isset( $_POST['fpvg_video_id'] ) ) {
			$video_id = absint( $_POST['fpvg_video_id'] );
			update_post_meta( $post_id, '_fpvg_video_id', $video_id );
		}

		// Sanitize and save the video position.
		if ( isset( $_POST['fpvg_video_position'] ) ) {
			$position = absint( $_POST['fpvg_video_position'] );
			// Ensure position is at least 1.
			$position = max( 1, $position );
			update_post_meta( $post_id, '_fpvg_video_position', $position );
		}
	}

	/**
	 * Enqueue admin scripts and styles.
	 *
	 * This method ensures that the necessary CSS and JavaScript files are loaded
	 * only on the product edit screen to avoid conflicts.
	 *
	 * @since 1.0
	 * @param string $hook The current admin page hook.
	 */
	public function enqueue_scripts( $hook ) {
		global $post;

		// Only load on product edit pages.
		if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
			return;
		}

		if ( ! is_object( $post ) || 'product' !== $post->post_type ) {
			return;
		}

		// Enqueue the WordPress media uploader scripts.
		wp_enqueue_media();

		// Enqueue scripts and styles for video preview.
		wp_enqueue_style( 'wp-mediaelement' );
		wp_enqueue_script( 'wp-mediaelement' );

		// Enqueue custom plugin admin stylesheet.
		wp_enqueue_style(
			'fpvg-admin-css',
			plugin_dir_url( __FILE__ ) . '../admin/css/fpvg-admin.css',
			array(),
			'1.0'
		);

		// Enqueue custom plugin admin script.
		wp_enqueue_script(
			'fpvg-admin-js',
			plugin_dir_url( __FILE__ ) . '../admin/js/fpvg-admin.js',
			array( 'jquery', 'wp-mediaelement' ),
			'1.0',
			true // Load in the footer.
		);
	}
}
