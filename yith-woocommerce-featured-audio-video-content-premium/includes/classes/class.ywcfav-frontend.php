<?php // phpcs:ignore WordPress.NamingConventions
/**
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @package YITH\WooCommerceFeaturedAudioandVideoContent
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'YITH_Featured_Audio_Video_Frontend' ) ) {

	/**
	 * YITH_Featured_Audio_Video_Frontend
	 */
	class YITH_Featured_Audio_Video_Frontend {
		/**
		 * Single instance of the class
		 *
		 * @var YITH_Featured_Audio_Video_Frontend $instance
		 */
		protected static $instance;
		/**
		 * Counter
		 *
		 * @var $counter
		 */
		protected $counter;

		/**
		 * __construct
		 *
		 * @return void
		 */
		public function __construct() {

			$this->counter = 0;
			add_filter(
				'woocommerce_single_product_image_thumbnail_html',
				array(
					$this,
					'get_video_audio_content',
				),
				10,
				2
			);

			add_action( 'wp_enqueue_scripts', array( $this, 'include_scripts' ), 10 );

		}


		/**
		 * Return single instance of class
		 *
		 * @author YITH <plugins@yithemes.com>
		 * @since 2.0.0
		 * @return YITH_Featured_Audio_Video_Frontend
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Get_video_audio_content
		 *
		 * @since 2.0.0
		 *
		 * @param string $html html.
		 * @param int    $post_thumbnail_id post_thumbnail_id.
		 *
		 * @return string
		 */
		public function get_video_audio_content( $html, $post_thumbnail_id ) {

			global $product;
			if ( 0 === $this->counter && $post_thumbnail_id === $product->get_image_id() ) {

				$video_args = YITH_Featured_Video_Manager()->get_featured_video_args( $product );

				if ( ! empty( $video_args ) ) {
					ob_start();
					wc_get_template( 'template_video.php', $video_args, YWCFAV_TEMPLATE_PATH, YWCFAV_TEMPLATE_PATH );
					$html = ob_get_contents();
					ob_end_clean();

					$this->counter ++;

				}
			}

			return $html;
		}

		/**
		 * Include_scripts
		 *
		 * @return void
		 */
		public function include_scripts() {

			$script_args = array(
				'zoom_enabled'                    => apply_filters( 'woocommerce_single_product_zoom_enabled', get_theme_support( 'wc-product-gallery-zoom' ) ),
				'zoom_options'                    => apply_filters( 'woocommerce_single_product_zoom_options', array() ),
				'img_class_container'             => '.' . ywcfav_get_gallery_item_class(),
				'thumbnail_gallery_class_element' => '.' . ywcfav_get_thumbnail_gallery_item(),
				'trigger_variation'               => apply_filters( 'ywcfav_trigger_variation_event', true ),
			);

			wp_register_script( 'ywcfav_frontend', YWCFAV_ASSETS_URL . 'js/' . yit_load_js_file( 'ywcfav_frontend.js' ), array( 'jquery' ), YWCFAV_VERSION, true );

			if ( is_product() ) {

				wp_localize_script( 'ywcfav_frontend', 'ywcfav_params', $script_args );
				wp_enqueue_script( 'ywcfav_frontend' );
			}
		}


	}
}

if ( ! function_exists( 'YITH_Featured_Audio_Video_Frontend' ) ) {

	/**
	 * YITH_Featured_Audio_Video_Frontend
	 *
	 * @return YITH_Featured_Audio_Video_Frontend|YITH_Featured_Audio_Video_Frontend_Premium
	 */
	function YITH_Featured_Audio_Video_Frontend() { // phpcs:ignore WordPress.NamingConventions
		$instance = null;
		if ( class_exists( 'YITH_Featured_Audio_Video_Frontend_Premium' ) ) {
			$instance = YITH_Featured_Audio_Video_Frontend_Premium::get_instance();
		} else {
			$instance = YITH_Featured_Audio_Video_Frontend::get_instance();
		}

		return $instance;
	}
}
