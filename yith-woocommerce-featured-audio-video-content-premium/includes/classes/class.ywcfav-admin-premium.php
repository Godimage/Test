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

if ( ! class_exists( 'YITH_Featured_Audio_Video_Admin_Premium' ) ) {

	/**
	 * YITH_Featured_Audio_Video_Admin_Premium
	 */
	class YITH_Featured_Audio_Video_Admin_Premium extends YITH_Featured_Audio_Video_Admin {
		/**
		 * Single instance of the class
		 *
		 * @var YITH_Featured_Audio_Video_Admin_Premium $instance
		 */
		protected static $instance;

		/**
		 * __construct
		 *
		 * @return void
		 */
		public function __construct() {
			parent::__construct();

			remove_action( 'woocommerce_product_options_general_product_data', array( $this, 'add_video_field' ) );
			add_action( 'admin_init', array( $this, 'save_audio_placeholder' ), 20 );
			add_filter( 'ywcfav_add_premium_tab', array( $this, 'add_premium_tab' ) );
			add_filter(
				'woocommerce_product_write_panel_tabs',
				array(
					$this,
					'print_audio_video_product_panels',
				),
				98
			);
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_premium_style_script' ) );

			// AJAX ACTION to add video and audio row
			// Save thumbn for video embedded via ajax!
			add_action( 'wp_ajax_save_thumbnail_video', array( $this, 'ajax_save_thumbnail_video' ) );
			// Add new video row!
			add_action( 'wp_ajax_add_new_video_row', array( $this, 'add_new_video_row' ) );
			// Add new audio row!
			add_action( 'wp_ajax_add_new_audio_row', array( $this, 'add_new_audio_row' ) );

			// Add new video row on variation!
			add_action( 'wp_ajax_add_new_video_variation', array( $this, 'add_new_video_variation' ) );

			//Import the video set in the free version
			add_action( 'wp_ajax_import_video_from_free', array( $this, 'import_video_from_free' ) );

			// Add metaboxes in woocommerce product!
			add_action( 'add_meta_boxes', array( $this, 'add_product_select_featured_content_meta_boxes' ) );

			add_action(
				'woocommerce_product_after_variable_attributes',
				array(
					$this,
					'print_variable_video_product',
				),
				20,
				3
			);

			add_action( 'woocommerce_save_product_variation', array( $this, 'save_product_variation_meta' ), 10, 2 );
		}

		/**
		 * Return single instance of class
		 *
		 * @return YITH_Featured_Audio_Video_Admin_Premium
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
		 * Save the audio placeholder
		 *
		 */
		public function save_audio_placeholder() {

			if ( apply_filters( 'ywcfav_generate_audio_placeholder', true ) ) {
				$audio_id  = get_option( 'ywcfav_audio_placeholder_id', false );
				$audio_src = false;

				if ( false !== $audio_id ) {
					$audio_src = wp_get_attachment_image_src( $audio_id );
				}

				if ( false === $audio_src ) {

					$audio_id = ywcfav_save_remote_image( YWCFAV_ASSETS_URL . 'images/audioplaceholder.jpg', 'audioplaceholder' );

					update_option( 'ywcfav_audio_placeholder_id', $audio_id );
				}
			}
		}

		/**
		 * Add_premium_tab
		 *
		 * @param array $tabs tabs.
		 *
		 * @return array
		 */
		public function add_premium_tab( $tabs ) {

			unset( $tabs['premium'] );
			$tabs['video-settings']   = __( 'Video Settings', 'yith-woocommerce-featured-video' );
			$tabs['audio-settings']   = __( 'Audio Settings', 'yith-woocommerce-featured-video' );
			$tabs['general-settings'] = __( 'Modal Settings', 'yith-woocommerce-featured-video' );
			$tabs['addon-settings']   = __( 'Gallery Settings', 'yith-woocommerce-featured-video' );

			return $tabs;
		}

		/**
		 * Print_audio_video_product_panels
		 *
		 * @return void
		 */
		public function print_audio_video_product_panels() {
			?>
            <style type="text/css">
                #woocommerce-product-data ul.wc-tabs .ywcfav_video_data_tab a:before, #woocommerce-product-data ul.wc-tabs .ywcfav_audio_data_tab a:before {
                    content: '';
                    display: none;
                }

            </style>
            <li class="ywcfav_video_data_tab">
                <a href="#ywcfav_video_data">
                    <i class="dashicons dashicons-video-alt2"></i>&nbsp;&nbsp;<?php esc_html_e( 'Video', 'yith-woocommerce-featured-video' ); ?>
                </a>
            </li>
            <li class="ywcfav_audio_data_tab">
                <a href="#ywcfav_audio_data">
                    <i class="dashicons dashicons-format-audio"></i>&nbsp;&nbsp;<?php esc_html_e( 'Audio', 'yith-woocommerce-featured-video' ); ?>
                </a>
            </li>

			<?php
			add_action( 'woocommerce_product_data_panels', array( $this, 'write_audio_video_product_panels' ) );
		}

		/**
		 * Write_audio_video_product_panels
		 *
		 * @return void
		 */
		public function write_audio_video_product_panels() {

			include_once YWCFAV_TEMPLATE_PATH . 'metaboxes/yith-wcfav-video-metabox.php';
			include_once YWCFAV_TEMPLATE_PATH . 'metaboxes/yith-wcfav-audio-metabox.php';
		}

		/**
		 * Enqueue_premium_style_script
		 *
		 * enqueue admin script
		 */
		public function enqueue_premium_style_script() {
			global $post;

			wp_register_script( 'ywcfav_script', YWCFAV_ASSETS_URL . 'js/' . yit_load_js_file( 'ywcfav_admin.js' ), array( 'jquery' ), time(), true );

			$video_placeholder_id = get_option( 'ywcfav_video_placeholder_id' );
			$audio_placeholder_id = get_option( 'ywcfav_audio_placeholder_id' );

			$ywcfav = array(
				'admin_url'                 => admin_url( 'admin-ajax.php', is_ssl() ? 'https' : 'http' ),
				'video_placeholder_img_src' => YWCFAV_ASSETS_URL . 'images/videoplaceholder.jpg',
				'audio_placeholder_img_src' => YWCFAV_ASSETS_URL . 'images/audioplaceholder.jpg',
				'video_placeholder_img_id'  => $video_placeholder_id,
				'audio_placeholder_img_id'  => $audio_placeholder_id,
				'error_video'               => __( 'Please select a Video', 'yith-woocommerce-featured-video' ),
				'actions'                   => array(
					'save_thumbnail_video'    => 'save_thumbnail_video',
					'add_new_video_row'       => 'add_new_video_row',
					'add_new_audio_row'       => 'add_new_audio_row',
					'add_new_video_variation' => 'add_new_video_variation',
					'import_video_from_free'  => 'import_video_from_free',
				),
			);

			wp_localize_script( 'ywcfav_script', 'ywcfav', $ywcfav );

			wp_register_style( 'ywcfav_admin_style', YWCFAV_ASSETS_URL . 'css/ywcfav_admin.css', array(), YWCFAV_VERSION );

			if ( ( isset( $_GET['page'] ) && 'yith_wc_featured_audio_video' === $_GET['page'] ) || ( isset( $post ) && 'product' === get_post_type( $post ) ) ) {  //phpcs:ignore WordPress.Security.NonceVerification
				wp_enqueue_script( 'ywcfav_script' );

				wp_enqueue_style( 'ywcfav_admin_style' );
			}
		}

		/**
		 * Plugin_row_meta
		 *
		 * Add the action links to plugin admin page
		 *
		 * @param mixed $new_row_meta_args new_row_meta_args.
		 * @param mixed $plugin_meta plugin_meta.
		 * @param mixed $plugin_file plugin_file.
		 * @param mixed $plugin_data plugin_data.
		 * @param mixed $status status.
		 * @param string $init_file init_file.
		 *
		 * @return   array
		 * @since    1.0
		 * @use plugin_row_meta
		 */
		public function plugin_row_meta( $new_row_meta_args, $plugin_meta, $plugin_file, $plugin_data, $status, $init_file = 'YWCFAV_INIT' ) {

			$new_row_meta_args = parent::plugin_row_meta( $new_row_meta_args, $plugin_meta, $plugin_file, $plugin_data, $status, $init_file );
			if ( defined( $init_file ) && constant( $init_file ) === $plugin_file ) {
				$new_row_meta_args['is_premium'] = true;
			}

			return $new_row_meta_args;
		}

		/**
		 * Add new video row in single product
		 *
		 * @since 2.0.0
		 */
		public function add_new_video_row() {

			if ( isset( $_POST['video_id'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification

				$video        = array(
					'name'     => isset( $_POST['video_name'] ) ? wp_unslash( $_POST['video_name'] ) : '',
					// phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
					'thumbn'   => isset( $_POST['video_img'] ) ? wp_unslash( $_POST['video_img'] ) : '',
					// phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
					'featured' => 'no',
					'id'       => isset( $_POST['video_id'] ) ? wp_unslash( $_POST['video_id'] ) : '',
					// phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
					'host'     => isset( $_POST['video_host'] ) ? wp_unslash( $_POST['video_host'] ) : '',
					// phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
					'content'  => isset( $_POST['video_content'] ) ? wp_unslash( $_POST['video_content'] ) : '',
					// phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
					'type'     => isset( $_POST['video_type'] ) ? wp_unslash( $_POST['video_type'] ) : '',
					// phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				);
				$video_params = array(
					'video_params' => $video,
					'loop'         => isset( $_POST['loop'] ) ? wp_unslash( $_POST['loop'] ) : '',
					// phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
					'product_id'   => isset( $_POST['product_id'] ) ? wp_unslash( $_POST['product_id'] ) : '',
					// phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				);

				ob_start();
				wc_get_template( 'metaboxes/views/html-product-video.php', $video_params, '', YWCFAV_TEMPLATE_PATH );
				$template = ob_get_contents();
				ob_end_clean();

				wp_send_json( array( 'result' => $template ) );
				die;

			}
		}

		/**
		 * Add new audio row in single product
		 *
		 * @since 1.2.0
		 */
		public function add_new_audio_row() {

			if ( isset( $_POST['audio_id'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification

				$audio = array(
					'name'     => isset( $_POST['audio_name'] ) ? wp_unslash( $_POST['audio_name'] ) : '',
					// phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
					'thumbn'   => isset( $_POST['audio_img'] ) ? wp_unslash( $_POST['audio_img'] ) : '',
					// phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
					'featured' => 'no',
					'id'       => isset( $_POST['audio_id'] ) ? wp_unslash( $_POST['audio_id'] ) : '',
					// phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
					'url'      => isset( $_POST['audio_content'] ) ? wp_unslash( $_POST['audio_content'] ) : '',
					// phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				);

				$audio_params = array(
					'audio_params' => $audio,
					'loop'         => isset( $_POST['loop'] ) ? wp_unslash( $_POST['loop'] ) : '',
					// phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
					'product_id'   => isset( $_POST['product_id'] ) ? wp_unslash( $_POST['product_id'] ) : '',
					// phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				);

				ob_start();
				wc_get_template( 'metaboxes/views/html-product-audio.php', $audio_params, '', YWCFAV_TEMPLATE_PATH );
				$template = ob_get_contents();
				ob_end_clean();

				wp_send_json( array( 'result' => $template ) );
				die;

			}

		}

		/**
		 * Call ajax for save thumbnail video
		 *
		 *
		 * @since 1.2.0
		 * @use wp_ajax_save_thumbnail_video
		 */
		public function ajax_save_thumbnail_video() {

			if ( isset( $_POST['ywcfav_host'] ) && isset( $_POST['ywcfav_id'] ) && apply_filters( 'ywcfav_generate_video_thumbnail', true ) ) { //phpcs:ignore WordPress.Security.NonceVerification

				$host       = sanitize_text_field( wp_unslash( $_POST['ywcfav_host'] ) ); //phpcs:ignore WordPress.Security.NonceVerification
				$video_id   = sanitize_text_field( wp_unslash( $_POST['ywcfav_id'] ) ); //phpcs:ignore WordPress.Security.NonceVerification
				$name       = isset( $_POST['ywcfav_name'] ) ? sanitize_text_field( wp_unslash( $_POST['ywcfav_name'] ) ) : ''; //phpcs:ignore WordPress.Security.NonceVerification
				$video_info = array(
					'name' => $name,
					'id'   => $video_id,
					'host' => $host,
				);

				$image_id = $this->save_video_thumbnail( $video_info );

				$result = 'no';

				if ( '' !== $image_id ) {
					$result = 'ok';

				}

				wp_send_json(
					array(
						'result' => $result,
						'id_img' => $image_id,
					)
				);
			}
		}

		/**
		 * Set_custom_product_meta
		 *
		 * @param WC_Product $product product.
		 *
		 * @since 1.2.0
		 */
		public function set_custom_product_meta( $product ) {

			if ( isset( $_POST['ywcfav_video'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification

				$product->update_meta_data( '_ywcfav_video', wp_unslash( $_POST['ywcfav_video'] ) ); // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			} else {

				$product->delete_meta_data( '_ywcfav_video' );
			}

			if ( isset( $_POST['ywcfav_audio'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification
				$product->update_meta_data( '_ywcfav_audio', wp_unslash( $_POST['ywcfav_audio'] ) ); // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

			} else {
				$product->delete_meta_data( '_ywcfav_audio' );
			}

			if ( isset( $_POST['ywcfav_select_featured'] ) && ! empty( $_POST['ywcfav_select_featured'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				$content = wp_unslash( $_POST['ywcfav_select_featured'] ); // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

				$type = ( strpos( $content, 'ywcfav_video' ) === false ) ? 'audio' : 'video';

				$args = array(
					'id'   => $content,
					'type' => $type,
				);

				$product->update_meta_data( '_ywcfav_featured_content', $args );
			} else {
				$product->delete_meta_data( '_ywcfav_featured_content' );
			}

		}

		/**
		 * Add product metabox
		 *
		 * @since 1.2.0
		 */
		public function add_product_select_featured_content_meta_boxes() {

			add_meta_box(
				'yith-ywcfav-metabox',
				__( 'Featured Video or Audio', 'yith-woocommerce-featured-video' ),
				array(
					$this,
					'featured_audio_video_meta_box_content',
				),
				'product',
				'side',
				'core'
			);
		}

		/**
		 * Print product metabox
		 *
		 * @since 1.2.0
		 */
		public function featured_audio_video_meta_box_content() {

			wc_get_template( 'metaboxes/yith-wcfav-select-video-featured-metabox.php', array(), YWCFAV_TEMPLATE_PATH, YWCFAV_TEMPLATE_PATH );
		}

		/**
		 * Print_variable_video_product
		 *
		 * @param int $loop loop.
		 * @param array $variation_data variation_data.
		 * @param WC_Product_Variation $variation variation.
		 */
		public function print_variable_video_product( $loop, $variation_data, $variation ) {

			$args                    = array(
				'loop'           => $loop,
				'variation_data' => $variation_data,
				'variation'      => $variation,
			);
			$args['video_variation'] = $args;

			wc_get_template( 'metaboxes/yith-wcfav-video-product-variations.php', $args, YWCFAV_TEMPLATE_PATH, YWCFAV_TEMPLATE_PATH );

		}

		/**
		 * Save variation meta
		 *
		 * @param int $variation_id variation id.
		 * @param int $i i.
		 *
		 */
		public function save_product_variation_meta( $variation_id, $i ) {
			$product = wc_get_product( $variation_id );

			if ( isset( $_POST['video_info'][ $i ] ) ) { //phpcs:ignore WordPress.Security.NonceVerification

				$product->update_meta_data( '_ywcfav_variation_video', wp_unslash( $_POST['video_info'][ $i ] ) ); // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

			} else {
				$product->delete_meta_data( '_ywcfav_variation_video' );
			}
			$product->save();
		}

		/**
		 * Add_new_video_variation
		 *
		 * @return void
		 */
		public function add_new_video_variation() {

			if ( isset( $_POST['video_id'] ) ) {//phpcs:ignore WordPress.Security.NonceVerification

				$video = array(
					'name'     => isset( $_POST['video_name'] ) ? wp_unslash( $_POST['video_name'] ) : '',
					// phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
					'thumbn'   => isset( $_POST['video_img'] ) ? wp_unslash( $_POST['video_img'] ) : '',
					// phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
					'featured' => 'no',
					'id'       => isset( $_POST['video_id'] ) ? wp_unslash( $_POST['video_id'] ) : '',
					// phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
					'host'     => isset( $_POST['video_host'] ) ? wp_unslash( $_POST['video_host'] ) : '',
					// phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
					'content'  => isset( $_POST['video_content'] ) ? wp_unslash( $_POST['video_content'] ) : '',
					// phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
					'type'     => isset( $_POST['video_type'] ) ? wp_unslash( $_POST['video_type'] ) : '',
					// phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

				);

				$video_params = array(
					'video_params' => $video,
					'loop'         => isset( $_POST['loop'] ) ? wp_unslash( $_POST['loop'] ) : '',
					// phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
					'product_id'   => isset( $_POST['product_id'] ) ? wp_unslash( $_POST['product_id'] ) : '',
					// phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				);

				ob_start();
				wc_get_template( 'metaboxes/views/html-product-variation-video.php', $video_params, '', YWCFAV_TEMPLATE_PATH );
				$template = ob_get_contents();
				ob_end_clean();

				wp_send_json( array( 'result' => $template ) );
				die;
			}
		}

		/**
		 * Import the video set in the Free version
		 *
		 * @since 1.5.0
		 */
		public function import_video_from_free() {
			$paged         = 1;
			$args          = array(
				'post_status' => 'publish',
				'post_type'   => 'product',
				'numberposts' => 15,
				'paged'       => $paged,
				'fields'      => 'ids',
				'meta_query'  => array( // phpcs:ignore WordPress.DB.SlowDBQuery
					array(
						'key'     => '_ywcfav_imported',
						'compare' => 'NOT EXISTS',
					),
				),
			);
			$products      = get_posts( $args );
			$thumbnail_id  = get_option( 'ywcfav_video_placeholder_id', '' );
			$tot_imported  = 0;
			$current_count = count( $products );
			while ( $current_count > 0 ) {
				foreach ( $products as $product_id ) {

					$free_video_url = get_post_meta( $product_id, '_video_url', true );
					if ( ! empty( $free_video_url ) ) {
						$host       = YIT_Video::video_id_by_url( $free_video_url );
						$host       = explode( ':', $host );
						$host       = isset( $host[0] ) ? $host[0] : '';
						$id         = 'ywcfav_video_id-' . uniqid();
						$video_args = array(
							array(
								'thumbn'   => $thumbnail_id,
								'type'     => 'url',
								'id'       => $id,
								'featured' => 'yes',
								'name'     => 'Video Free',
								'content'  => $free_video_url,
								'host'     => $host,
							)
						);

						$current_video = get_post_meta( $product_id, '_ywcfav_video', true );

						if ( ! empty( $current_video ) ) {
							$video_args['featured'] = 'no';
							$video_args             = array_merge( $current_video, $video_args );
						} else {
							update_post_meta( $product_id, '_ywcfav_featured_content', array(
								'id'   => $id,
								'type' => 'video',
							) );
						}
						update_post_meta( $product_id, '_ywcfav_video', $video_args );
						$tot_imported ++;
					}
					update_post_meta( $product_id, '_ywcfav_imported', 'yes' );
				}
				$args['paged'] = $paged ++;

				$products      = get_posts( $args );
				$current_count = count( $products );
			}

			/* translators: %s is the amount of video imported*/
			$result = sprintf( _n( '%s video was imported', '%s video were imported', $tot_imported, 'yith-woocommerce-featured-video' ), $tot_imported );

			wp_send_json( $result );
			die();
		}
	}
}
