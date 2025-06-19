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

if ( ! class_exists( 'YITH_WC_Audio_Video' ) ) {

	/**
	 * YITH_WC_Audio_Video
	 */
	class YITH_WC_Audio_Video {
		/**
		 * Single instance of the class
		 *
		 * @var YITH_WC_Audio_Video $instance
		 */
		protected static $instance;
		/**
		 * __construct
		 *
		 * @return void
		 */
		public function __construct() {

			add_action( 'wp_enqueue_scripts', array( $this, 'include_video_scripts' ), 20 );
			YITH_Featured_Video_Manager();
			add_action( 'init', 'YITH_FAV_Load_Themes_Integration', 20 );

			if ( ! is_admin() || defined( 'DOING_AJAX' ) ) {

				if ( ( ywcfav_check_is_zoom_magnifier_is_active() && ! ywcfav_check_is_product_is_exclude_from_zoom() ) ) {

					YITH_Featured_Audio_Video_Zoom_Magnifier();
				} else {

					YITH_Featured_Audio_Video_Frontend();
				}
				if ( defined( 'YITH_WCQV_PREMIUM' ) ) {

					YITH_FAV_Quick_View_Module();
				}
			}

			if ( is_admin() ) {
				YITH_Featured_Audio_Video_Admin();
			}

			add_action( 'wp_loaded', array( $this, 'register_plugin_for_activation' ), 99 );
			add_action( 'wp_loaded', array( $this, 'register_plugin_for_updates' ), 99 );

		}


		/**
		 * Load_right_class
		 *
		 * @return void
		 */
		public function load_right_class() {

		}

		/**
		 * Return single instance of class
		 *
		 * @return YITH_WC_Audio_Video
		 * @since 2.0.0
		 * @author YITH <plugins@yithemes.com>
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Include_video_scripts
		 *
		 * @return void
		 */
		public function include_video_scripts() {

			if ( is_product() || defined( 'YITH_WCQV_PREMIUM' ) ) {

				wp_enqueue_style( 'videojs', YWCFAV_ASSETS_URL . 'css/videojs/video-js.min.css', array(), YWCFAV_VERSION );
				wp_enqueue_style( 'venobox_style', YWCFAV_ASSETS_URL . '/css/venobox.css', array(), true );

				wp_enqueue_script( 'venobox_api', YWCFAV_ASSETS_URL . 'js/lib/venobox/jquery.venobox.js', array( 'jquery' ), array(), true );
				wp_enqueue_script( 'vimeo-api', YWCFAV_ASSETS_URL . 'js/lib/vimeo/vimeo_player.js', array(), YWCFAV_VERSION, true );
				wp_register_script( 'videojs', YWCFAV_ASSETS_URL . 'js/lib/videojs/video.js', array( 'jquery' ), YWCFAV_VERSION, true );
				wp_enqueue_script( 'soundcloud', YWCFAV_ASSETS_URL . 'js/lib/soundcloud/soundcloud.js', array(), YWCFAV_VERSION, true );

				wp_enqueue_style( 'ywcfav_style', YWCFAV_ASSETS_URL . 'css/ywcfav_frontend.css', array(), YWCFAV_VERSION );

				wp_enqueue_script(
					'ywcfav_content_manager',
					YWCFAV_ASSETS_URL . 'js/' . yit_load_js_file( 'ywcfav_content_manager.js' ),
					array(
						'jquery',
						'videojs',
						'vimeo-api',
					),
					YWCFAV_VERSION,
					true
				);

				$script_args = array(
					'product_gallery_trigger_class' => '.' . ywcfav_get_product_gallery_trigger(),
					'current_slide_active_class'    => '.' . ywcfav_get_current_slider_class(),
					'autoplay'                      => get_option( 'ywcfav_autoplay', 'no' ),
				);

				wp_localize_script( 'ywcfav_content_manager', 'ywcfav_args', $script_args );

				wp_enqueue_script( 'ywcfav_owl_carousel', YWCFAV_ASSETS_URL . '/js/lib/owl/owl.carousel.min.js', array( 'jquery' ), YWCFAV_VERSION, true );
				wp_enqueue_style( 'ywcfav_owl_carousel_style', YWCFAV_ASSETS_URL . '/css/owl-carousel/owl.carousel.css', array(), YWCFAV_VERSION );

				wp_enqueue_script(
					'ywcfav_slider',
					YWCFAV_ASSETS_URL . 'js/' . yit_load_js_file( 'ywcfav_slider.js' ),
					array(
						'jquery',
						'venobox_api',
					),
					YWCFAV_VERSION,
					true
				);

				$effect = get_option( 'ywcfav_modal_effect' );

				if ( $effect > 0 ) {
					wp_enqueue_style( 'venobox_effects', YWCFAV_ASSETS_URL . 'css/effects/effect-' . $effect . '.css', array(), YWCFAV_VERSION );
				}
			}
		}

		/**
		 * This method is deprecated, valid for old custom codes
		 *
		 *
		 * @deprecated since 1.2.0 Use YITH_Featured_Video_Manager()->woocommerce_show_product_video_thumbnails
		 */
		public function woocommerce_show_product_video_thumbnails() {
			_deprecated_function( __METHOD__, '1.2.0', 'YITH_Featured_Video_Manager()->woocommerce_show_product_video_thumbnails()' );
			YITH_Featured_Video_Manager()->woocommerce_show_product_video_thumbnails();
		}

		/**
		 * This method is deprecated, valid for old custom codes
		 *
		 *
		 * @deprecated since 1.2.0 Use YITH_Featured_Video_Manager()->woocommerce_show_product_audio_thumbnails
		 */
		public function woocommerce_show_product_audio_thumbnails() {
			_deprecated_function( __METHOD__, '1.2.0', 'YITH_Featured_Video_Manager()->woocommerce_show_product_audio_thumbnails()' );
			YITH_Featured_Video_Manager()->woocommerce_show_product_audio_thumbnails();
		}

		/**
		 * Register plugins for activation tab
		 *
		 * @return void
		 * @since    1.0.0
		 */
		public function register_plugin_for_activation() {
			if ( ! class_exists( 'YIT_Plugin_Licence' ) ) {
				require_once YWCFAV_DIR . 'plugin-fw/licence/lib/yit-licence.php';
				require_once YWCFAV_DIR . 'plugin-fw/licence/lib/yit-plugin-licence.php';
			}
			YIT_Plugin_Licence()->register( YWCFAV_INIT, YWCFAV_SECRET_KEY, YWCFAV_SLUG );
		}

		/**
		 * Register plugins for update tab
		 *
		 * @return void
		 * @since    1.0.0
		 */
		public function register_plugin_for_updates() {
			if ( ! class_exists( 'YIT_Upgrade' ) ) {
				require_once YWCFAV_DIR . 'plugin-fw/lib/yit-upgrade.php';
			}
			YIT_Upgrade()->register( YWCFAV_SLUG, YWCFAV_INIT );
		}

	}

}


if ( ! function_exists( 'YITH_Featured_Video' ) ) {
	/**
	 * YITH_Featured_Video
	 *
	 * @return instance
	 */
	function YITH_Featured_Video() { // phpcs:ignore WordPress.NamingConventions
		return YITH_WC_Audio_Video::get_instance();
	}
}
