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

if ( ! class_exists( 'YITH_FAV_Quick_View_Module' ) ) {

	/**
	 * YITH_FAV_Quick_View_Module
	 */
	class YITH_FAV_Quick_View_Module {

		/**
		 * __construct
		 *
		 * @return void
		 */
		public function __construct() {

			add_filter( 'yith_wcqv_get_main_image_id', array( $this, 'get_featured_thumbnail_id' ), 10, 2 );
			add_action( 'wp_enqueue_scripts', array( $this, 'add_scripts' ), 99 );

			add_action( 'wp_ajax_get_featured_content', array( $this, 'get_featured_content' ) );
			add_action( 'wp_ajax_nopriv_get_featured_content', array( $this, 'get_featured_content' ) );

			add_action( 'wp_ajax_nopriv_get_variation_content', array( $this, 'get_variation_content' ) );
			add_action( 'wp_ajax_get_variation_content', array( $this, 'get_variation_content' ) );
		}

		/**
		 * Get_featured_thumbnail_id
		 *
		 * @param  mixed $thumbnail_id thumbnail id.
		 * @param  mixed $product_id product id.
		 * @return $thumbnail_id
		 */
		public function get_featured_thumbnail_id( $thumbnail_id, $product_id ) {
			$product    = wc_get_product( $product_id );
			$video_args = YITH_Featured_Video_Manager()->get_featured_args( $product );

			if ( ! empty( $video_args ) ) {

				$thumbnail_id = $video_args['thumbnail_id'];
			}

			return $thumbnail_id;
		}


		/**
		 * Add_scripts
		 *
		 * @return void
		 */
		public function add_scripts() {

			wp_register_script(
				'ywcfav_quick_view',
				YWCFAV_ASSETS_URL . 'js/' . yit_load_js_file( 'ywcfav_quick_view.js' ),
				array(
					'jquery',
					'ywcfav_content_manager',
				),
				YWCFAV_VERSION,
				true
			);
			$script_args = array(

				'img_class_container'             => '.' . ywcfav_get_gallery_item_class(),
				'thumbnail_gallery_class_element' => '.' . ywcfav_get_thumbnail_gallery_item(),
				'admin_url'                       => admin_url( 'admin-ajax.php', is_ssl() ? 'https' : 'http' ),
				'actions'                         => array(
					'get_variation_content' => 'get_variation_content',
					'get_featured_content'  => 'get_featured_content',
				),
			);

			wp_localize_script( 'ywcfav_quick_view', 'ywcfav_quick_params', $script_args );
			wp_enqueue_script( 'ywcfav_quick_view' );
		}

		/**
		 * Get_featured_content
		 *
		 * @return void
		 */
		public function get_featured_content() {

			if ( isset( $_REQUEST['product_id'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification

				$product_id                        = sanitize_text_field( wp_unslash( $_REQUEST['product_id'] ) ); //phpcs:ignore WordPress.Security.NonceVerification
				$product                           = wc_get_product( $product_id );
				$args                              = YITH_Featured_Video_Manager()->get_featured_args( $product );
				$args['featured_content_selected'] = true;

				if ( ! empty( $args ) ) {

					if ( isset( $args['host'] ) && 'audio' !== $args['host'] ) {
						$template_name = 'template_video.php';
					} else {
						$template_name = 'template_audio.php';
					}

					ob_start();
					wc_get_template( $template_name, $args, YWCFAV_TEMPLATE_PATH, YWCFAV_TEMPLATE_PATH );
					$html = ob_get_contents();
					ob_end_clean();

					wp_send_json( $html );
				}
			}
		}

		/**
		 * Get_variation_content
		 *
		 * @return void
		 */
		public function get_variation_content() {

			if ( isset( $_REQUEST['variation_id'] ) && isset( $_REQUEST['content_id'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification

				$product    = wc_get_product( sanitize_text_field( wp_unslash( $_REQUEST['variation_id'] ) ) ); //phpcs:ignore WordPress.Security.NonceVerification
				$content_id = sanitize_text_field( wp_unslash( $_REQUEST['content_id'] ) ); //phpcs:ignore WordPress.Security.NonceVerification
				$content    = YITH_Featured_Video_Manager()->find_featured_video( $product, $content_id );
				if ( ! empty( $content ) ) {
					if ( 'url' === $content['type'] ) {
						list( $host, $video_id ) = explode( ':', ywcfav_video_type_by_url( $content['content'] ) );

					} else {
						$video_id = $content['content'];
					}

					$video_args = array(
						'id'                        => $content['id'],
						'video_id'                  => $video_id,
						'host'                      => $content['host'],
						'thumbnail_id'              => $content['thumbn'],
						'featured_content_selected' => true,
						'product_id'                => sanitize_text_field( wp_unslash( $_REQUEST['variation_id'] ) ), //phpcs:ignore WordPress.Security.NonceVerification

					);
					$args = YITH_Featured_Video_Manager()->get_featured_video_args( $product, $content );
					$args = array_merge( $video_args, $args );

					$html = YITH_Featured_Video_Manager()->get_featured_template( $args );

					wp_send_json( $html );
				}
			}
		}
	}
}

/**
 * YITH_FAV_Quick_View_Module
 *
 * @return YITH_FAV_Quick_View_Moduleoid
 */
function YITH_FAV_Quick_View_Module() { // phpcs:ignore WordPress.NamingConventions
	return new YITH_FAV_Quick_View_Module();
}
