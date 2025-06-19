<?php
/**
 * FPVG_Frontend Class
 *
 * This class is responsible for all front-end functionality of the Flatsome Product Video Gallery plugin.
 * It handles script enqueueing, and the logic for injecting the video into the product gallery
 * by leveraging both standard WooCommerce hooks and a Flatsome-specific theme filter for
 * maximum compatibility and stability.
 *
 * @package    Flatsome_Product_Video_Gallery
 * @subpackage Flatsome_Product_Video_Gallery/includes
 * @author     Neo
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FPVG_Frontend {

	/**
	 * A unique placeholder string used to identify the video's position within the gallery array.
	 * This is not a real attachment ID and is used internally by the plugin.
	 *
	 * @since 1.0
	 * @var string
	 */
	const VIDEO_PLACEHOLDER = 'fpvg_video_placeholder';

	/**
	 * An HTML comment used as a temporary marker in the gallery's HTML output.
	 * This marker is safely injected and then replaced with the final <video> tag,
	 * preventing interference with Flatsome's gallery slider initialization.
	 *
	 * @since 1.0
	 * @var string
	 */
	const VIDEO_HTML_MARKER = '<!-- FPVG_VIDEO_REPLACEMENT_MARKER -->';

	/**
	 * Class constructor.
	 *
	 * Initializes the class and adds all necessary WordPress and Flatsome hooks to
	 * integrate the video functionality into the front-end product page.
	 *
	 * @since 1.0
	 */
	public function __construct() {
		// Enqueue frontend CSS and JavaScript assets.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );

		// Step 1: Inject our placeholder into the array of gallery image IDs.
		add_filter( 'woocommerce_product_get_gallery_image_ids', array( $this, 'add_video_placeholder_to_gallery_ids' ), 20, 2 );

		// Step 2: Intercept thumbnail generation to output our HTML marker for the placeholder.
		add_filter( 'woocommerce_single_product_image_thumbnail_html', array( $this, 'generate_html_marker' ), 10, 2 );

		// Step 3: Use Flatsome's final gallery HTML filter to replace our marker with the actual video tag.
		add_filter( 'flatsome_single_product_images_html', array( $this, 'replace_marker_with_video_html' ), 20, 2 );
	}

	/**
	 * Enqueues frontend scripts and styles.
	 *
	 * This method ensures that the plugin's CSS and JavaScript are loaded only on single
	 * product pages and only when a product video is actually present.
	 *
	 * @since 1.0
	 */
	public function enqueue_assets() {
		// Only run on single product pages.
		if ( ! is_product() ) {
			return;
		}

		global $product;
		if ( ! is_a( $product, 'WC_Product' ) ) {
			return;
		}

		// Check if a video ID is saved for the current product.
		$video_id = get_post_meta( $product->get_id(), '_fpvg_video_id', true );
		if ( empty( $video_id ) ) {
			return;
		}

		// Enqueue frontend stylesheet.
		wp_enqueue_style(
			'fpvg-frontend-css',
			plugin_dir_url( __FILE__ ) . '../public/css/fpvg-frontend.css',
			array(),
			'1.0'
		);

		// Enqueue frontend JavaScript.
		wp_enqueue_script(
			'fpvg-frontend-js',
			plugin_dir_url( __FILE__ ) . '../public/js/fpvg-frontend.js',
			array( 'jquery' ),
			'1.0',
			true // Load in the footer.
		);
	}

	/**
	 * Adds a video placeholder to the product gallery image IDs array.
	 *
	 * This function hooks into WooCommerce to insert a unique string (`fpvg_video_placeholder`)
	 * into the gallery image array at the position specified by the user.
	 *
	 * @since 1.0
	 * @param array      $image_ids Array of gallery image attachment IDs.
	 * @param WC_Product $product   The current product object.
	 * @return array The modified array of image IDs including our placeholder.
	 */
	public function add_video_placeholder_to_gallery_ids( $image_ids, $product ) {
		if ( ! is_a( $product, 'WC_Product' ) ) {
			return $image_ids;
		}

		$video_id = get_post_meta( $product->get_id(), '_fpvg_video_id', true );

		// If no video is set for this product, return the original IDs.
		if ( empty( $video_id ) ) {
			return $image_ids;
		}

		$position = get_post_meta( $product->get_id(), '_fpvg_video_position', true );
		$position = ! empty( $position ) ? absint( $position ) : 1; // Default to 1.

		// Convert the 1-based position from user input to a 0-based array index.
		$position_index = max( 0, $position - 1 );

		// Inject our placeholder string into the array at the correct index.
		array_splice( $image_ids, $position_index, 0, self::VIDEO_PLACEHOLDER );

		return $image_ids;
	}

	/**
	 * Generates a unique HTML marker for our video placeholder.
	 *
	 * When WooCommerce generates the HTML for each gallery thumbnail, this function
	 * checks if the current item is our video placeholder. If it is, it returns
	 * a simple HTML comment instead of an `<img>` tag. This avoids broken image
	 * icons and provides a safe, find-and-replace target for the next step.
	 *
	 * @since 1.0
	 * @param string     $html          The default WooCommerce thumbnail HTML.
	 * @param int|string $attachment_id The attachment ID, or our placeholder string.
	 * @return string The original HTML, or our unique marker if it's the video placeholder.
	 */
	public function generate_html_marker( $html, $attachment_id ) {
		if ( self::VIDEO_PLACEHOLDER === $attachment_id ) {
			return self::VIDEO_HTML_MARKER;
		}
		return $html;
	}

	/**
	 * Replaces the HTML marker with the final <video> tag.
	 *
	 * This function hooks into a Flatsome-specific filter that runs after the entire
	 * gallery HTML has been constructed. It finds our unique HTML marker and replaces it
	 * with the complete HTML5 video element. This is the critical step that ensures
	 * perfect integration with Flatsome's gallery layouts (e.g., Stack - Featured 2 columns).
	 *
	 * @since 1.0
	 * @param string $html       The complete gallery HTML generated by Flatsome.
	 * @param int    $product_id The current product ID.
	 * @return string The modified gallery HTML, now containing the video.
	 */
	public function replace_marker_with_video_html( $html, $product_id ) {
		// Only proceed if our marker is found in the gallery HTML.
		if ( false === strpos( $html, self::VIDEO_HTML_MARKER ) ) {
			return $html;
		}

		// Retrieve the video data.
		$video_id = get_post_meta( $product_id, '_fpvg_video_id', true );
		$video_url = $video_id ? wp_get_attachment_url( $video_id ) : false;

		// If the video data is missing, simply remove the marker and return.
		if ( ! $video_url ) {
			return str_replace( self::VIDEO_HTML_MARKER, '', $html );
		}

		// Use the product's featured image as the video's poster (still frame).
		$poster_url  = get_the_post_thumbnail_url( $product_id, 'woocommerce_single' );
		$poster_attr = $poster_url ? 'poster="' . esc_url( $poster_url ) . '"' : '';

		// Construct the final HTML5 <video> tag.
		// Attributes are set for a seamless gallery preview experience (autoplay, loop, mute).
		// The custom data attribute allows for easy targeting with JavaScript.
		$video_tag = sprintf(
			'<video class="fpvg-video" src="%s" %s playsinline loop muted data-fpvg-video="true"></video>',
			esc_url( $video_url ),
			$poster_attr
		);

		// Replace our marker with the fully constructed video tag.
		return str_replace( self::VIDEO_HTML_MARKER, $video_tag, $html );
	}
}
