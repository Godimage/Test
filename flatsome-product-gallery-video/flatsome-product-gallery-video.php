<?php
/**
 * Plugin Name:       Flatsome Product Gallery Video
 * Plugin URI:        https://example.com/flatsome-product-gallery-video
 * Description:       Adds a video to the Flatsome theme's product gallery using a stable, compatible integration method.
 * Version:           2.0.0
 * Author:            Expert Analyst
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

	private static $instance;

	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

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

	public function render_video_meta_box( $post ) {
		wp_nonce_field( 'fvg_save_video_meta_data', 'fvg_video_nonce' );

		$video_id      = get_post_meta( $post->ID, '_fvg_video_id', true );
		$thumbnail_id  = get_post_meta( $post->ID, '_fvg_thumbnail_id', true );
		$auto_resize   = get_post_meta( $post->ID, '_fvg_auto_resize', true );
		$thumbnail_url = $thumbnail_id ? wp_get_attachment_image_url( (int) $thumbnail_id, 'thumbnail' ) : '';
		$video_filename = $video_id ? basename( wp_get_attachment_url( (int) $video_id ) ) : '';
		?>
		<div class="fvg-meta-box-wrapper">
			<!-- Video File -->
			<p>
				<strong><?php esc_html_e( 'Video File (MP4)', 'fvg' ); ?></strong><br>
				<span id="fvg_video_file_name"><?php echo esc_html( $video_filename ); ?></span>
				<input type="hidden" id="fvg_video_id" name="fvg_video_id" value="<?php echo esc_attr( $video_id ); ?>" />
				<button type="button" id="fvg_upload_video_button" class="button" style="<?php echo ! empty( $video_id ) ? 'display:none;' : ''; ?>"><?php esc_html_e( 'Upload Video', 'fvg' ); ?></button>
				<button type="button" id="fvg_remove_video_button" class="button" style="<?php echo empty( $video_id ) ? 'display:none;' : ''; ?>"><?php esc_html_e( 'Remove Video', 'fvg' ); ?></button>
			</p>

			<!-- Video Thumbnail -->
			<p>
				<strong><?php esc_html_e( 'Video Thumbnail Image', 'fvg' ); ?></strong><br>
				<div id="fvg_thumbnail_preview" style="margin-bottom: 5px;">
					<?php if ( $thumbnail_url ) : ?>
						<img src="<?php echo esc_url( $thumbnail_url ); ?>" style="max-width:100%; height:auto;" />
					<?php endif; ?>
				</div>
				<input type="hidden" id="fvg_thumbnail_id" name="fvg_thumbnail_id" value="<?php echo esc_attr( $thumbnail_id ); ?>" />
				<button type="button" id="fvg_upload_thumbnail_button" class="button" style="<?php echo ! empty( $thumbnail_id ) ? 'display:none;' : ''; ?>"><?php esc_html_e( 'Upload Thumbnail', 'fvg' ); ?></button>
				<button type="button" id="fvg_remove_thumbnail_button" class="button" style="<?php echo empty( $thumbnail_id ) ? 'display:none;' : ''; ?>"><?php esc_html_e( 'Remove Thumbnail', 'fvg' ); ?></button>
			</p>

			<!-- Auto Resize Checkbox -->
			<p>
				<label for="fvg_auto_resize">
					<input type="checkbox" id="fvg_auto_resize" name="fvg_auto_resize" value="1" <?php checked( $auto_resize, '1' ); ?> />
					<?php esc_html_e( 'Auto Resize Video Lightbox', 'fvg' ); ?>
				</label>
				<span class="description" style="display: block;"><?php esc_html_e( 'Fit video to browser window.', 'fvg' ); ?></span>
			</p>
		</div>
		<?php
	}

	public function save_video_meta_data( $post_id ) {
		if ( ! isset( $_POST['fvg_video_nonce'] ) || ! wp_verify_nonce( $_POST['fvg_video_nonce'], 'fvg_save_video_meta_data' ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		update_post_meta( $post_id, '_fvg_video_id', isset( $_POST['fvg_video_id'] ) ? absint( $_POST['fvg_video_id'] ) : '' );
		update_post_meta( $post_id, '_fvg_thumbnail_id', isset( $_POST['fvg_thumbnail_id'] ) ? absint( $_POST['fvg_thumbnail_id'] ) : '' );
		$auto_resize = isset( $_POST['fvg_auto_resize'] ) ? '1' : '0';
		update_post_meta( $post_id, '_fvg_auto_resize', $auto_resize );
	}

	public function enqueue_admin_assets( $hook ) {
		global $post;
		if ( ( 'post.php' === $hook || 'post-new.php' === $hook ) && isset( $post->post_type ) && 'product' === $post->post_type ) {
			wp_enqueue_media();
			wp_enqueue_script(
				'fvg-admin-js',
				plugin_dir_url( __FILE__ ) . 'js/admin.js',
				array( 'jquery' ),
				'2.0.0',
				true
			);
		}
	}

	public function enqueue_frontend_assets() {
		if ( is_product() ) {
			wp_enqueue_style(
				'fvg-style',
				plugin_dir_url( __FILE__ ) . 'css/style.css',
				array(),
				'2.0.0'
			);
			wp_enqueue_script(
				'fvg-frontend-js',
				plugin_dir_url( __FILE__ ) . 'js/frontend.js',
				array( 'jquery', 'flatsome-main' ),
				'2.0.0',
				true
			);
		}
	}

	public function add_video_thumbnail_to_gallery( $image_ids, $product ) {
		if ( ! is_a( $product, 'WC_Product' ) ) {
			return $image_ids;
		}
		$thumbnail_id = get_post_meta( $product->get_id(), '_fvg_thumbnail_id', true );
		if ( ! empty( $thumbnail_id ) ) {
			array_unshift( $image_ids, $thumbnail_id );
		}
		return array_unique( $image_ids );
	}

	public function add_video_data_to_thumbnail_html( $html, $attachment_id ) {
		global $product;
		if ( ! is_a( $product, 'WC_Product' ) ) {
			return $html;
		}
		$video_thumbnail_id = get_post_meta( $product->get_id(), '_fvg_thumbnail_id', true );
		if ( ! empty( $video_thumbnail_id ) && (int) $attachment_id === (int) $video_thumbnail_id ) {
			$video_id = get_post_meta( $product->get_id(), '_fvg_video_id', true );
			if ( empty( $video_id ) ) {
				return $html;
			}
			$video_url   = wp_get_attachment_url( $video_id );
			$auto_resize = get_post_meta( $product->get_id(), '_fvg_auto_resize', true ) ? 'true' : 'false';

			if ( ! empty( $video_url ) ) {
				$html = str_replace( 'class="', 'class="fvg-video-thumbnail ', $html );
				$data_attributes = sprintf(
					' data-fvg-video-url="%s" data-fvg-auto-resize="%s" ',
					esc_url( $video_url ),
					esc_attr( $auto_resize )
				);
				$html = str_replace( '<a ', '<a' . $data_attributes, $html );
			}
		}
		return $html;
	}
}

Flatsome_Product_Gallery_Video::get_instance();
