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
if ( ! class_exists( 'YITH_Featured_Audio_Video_Frontend_Premium' ) ) {

	/**
	 * YITH_Featured_Audio_Video_Frontend_Premium
	 */
	class YITH_Featured_Audio_Video_Frontend_Premium extends YITH_Featured_Audio_Video_Frontend {
		/**
		 * Single instance of the class
		 *
		 * @var YITH_Featured_Audio_Video_Frontend_Premium $instance
		 */
		protected static $instance;
		/**
		 * Counter
		 *
		 * @var YITH_Featured_Audio_Video_Frontend_Premium $counter
		 */
		protected $counter;

		/**
		 * __construct
		 *
		 * @return void
		 */
		public function __construct() { // phpcs:ignore

			parent::__construct();

		}


		/**
		 * Return single instance of class
		 *
		 * @return YITH_Featured_Audio_Video_Frontend_Premium
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
		 * Get_video_audio_content
		 *
		 * @param string $html html.
		 * @param int    $post_thumbnail_id post_thumbnail_id.
		 *
		 * @return string
		 * @since 2.0.0
		 *
		 */
		public function get_video_audio_content( $html, $post_thumbnail_id = false ) {

			global $product;

			if ( ! $product  ) {
				return $html;
			}

            $gallery_mode  = get_option( 'ywcfav_gallery_mode', 'plugin_gallery' );
            $featured_info = $product->get_meta( '_ywcfav_featured_content', true );

            if ( ! ywcfav_product_has_featured_content( $product ) || ( 'plugin_gallery' === $gallery_mode && empty( $featured_info ) ) ) {
                return $html;
            }

			if ( 0 === $this->counter && ( ! $post_thumbnail_id || ywcfav_product_has_featured_content( $product ) ) ) {

				$video_args = YITH_Featured_Video_Manager()->get_featured_args( $product );

					if ( ! apply_filters( 'yith_fav_include_featured_image', false, $html, $post_thumbnail_id ) ) {
						$the_html = YITH_Featured_Video_Manager()->get_featured_template( $video_args );
						$html     = empty( $the_html ) ? $html : $the_html;
					} else {
						$featured_image_html = $html;
						$html                = YITH_Featured_Video_Manager()->get_featured_template( $video_args );
						$html               .= $featured_image_html;
					}

					$this->counter ++;
			}

			return $html;
		}

	}
}
