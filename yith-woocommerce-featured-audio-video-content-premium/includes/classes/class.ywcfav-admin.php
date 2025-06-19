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

if ( ! class_exists( 'YITH_Featured_Audio_Video_Admin' ) ) {

	/**
	 * YITH_Featured_Audio_Video_Admin
	 */
	class  YITH_Featured_Audio_Video_Admin {
		/**
		 * Single instance of the class
		 *
		 * @var YITH_Featured_Audio_Video_Admin $instance
		 */
		protected static $instance;
		/**
		 * YITH_Featured_Audio_Video_Admin panel
		 *
		 * @var YITH_Featured_Audio_Video_Admin $panel
		 */
		protected $panel;
		/**
		 * YITH_Featured_Audio_Video_Admin panel page
		 *
		 * @var YITH_Featured_Audio_Video_Admin $panel_page
		 */
		protected $panel_page;
		/**
		 * YITH_Featured_Audio_Video_Admin premium
		 *
		 * @var YITH_Featured_Audio_Video_Admin $premium
		 */
		protected $premium;

		/**
		 * __construct
		 *
		 * @return void
		 */
		public function __construct() {

			$this->panel      = null;
			$this->panel_page = 'yith_wc_featured_audio_video';
			$this->premium    = 'premium.php';

			// Add action links!
			add_filter(
				'plugin_action_links_' . plugin_basename( YWCFAV_DIR . '/' . basename( YWCFAV_FILE ) ),
				array(
					$this,
					'action_links',
				)
			);
			// Add row meta!
			add_filter( 'yith_show_plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 5 );

			add_action( 'yith_wc_featured_audio_video_premium', array( $this, 'premium_tab' ) );
			add_action( 'admin_menu', array( $this, 'add_ywcfav_menu' ), 5 );

			add_action( 'woocommerce_product_options_general_product_data', array( $this, 'add_video_field' ) );

			add_action( 'woocommerce_admin_process_product_object', array( $this, 'set_custom_product_meta' ), 10, 1 );

			add_action( 'admin_init', array( $this, 'save_video_placeholder' ), 20 );
		}

		/**
		 * Return single instance of class
		 *
		 * @return YITH_Featured_Audio_Video_Admin
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
		 * Action Links
		 *
		 * Add the action links to plugin admin page
		 *
		 * @param mixed $links | links plugin array.
		 *
		 * @return   mixed Array
		 * @since    1.0
		 * @use plugin_action_links_{$plugin_file_name}
		 */
		public function action_links( $links ) {
			$is_premium = defined( 'YWCFAV_PREMIUM' );
			$links      = yith_add_action_links( $links, $this->panel_page, $is_premium, 'yith-woocommerce-featured-audio-video-content' );

			return $links;
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
		public function plugin_row_meta( $new_row_meta_args, $plugin_meta, $plugin_file, $plugin_data, $status, $init_file = 'YWCFAV_FREE_INIT' ) {

			if ( defined( $init_file ) && constant( $init_file ) === $plugin_file ) {
				$new_row_meta_args['slug'] = 'yith-woocommerce-featured-audio-video-content';

			}

			if ( defined( 'YWCFAV_FREE_INIT' ) && YWCFAV_FREE_INIT === $plugin_file ) {
				$new_row_meta_args['support'] = array(
					'url' => 'https://wordpress.org/support/plugin/yith-woocommerce-featured-video',
				);
			}

			return $new_row_meta_args;
		}


		/**
		 * Premium Tab Template
		 *
		 * Load the premium tab template on admin page
		 *
		 * @return  void
		 * @since   1.0.0
		 */
		public function premium_tab() {
			$premium_tab_template = YWCFAV_TEMPLATE_PATH . '/admin/' . $this->premium;
			if ( file_exists( $premium_tab_template ) ) {
				include_once $premium_tab_template;
			}
		}

		/**
		 * Add a panel under YITH Plugins tab
		 *
		 * @return   void
		 * @since    1.0
		 * @use     /Yit_Plugin_Panel class
		 * @see      plugin-fw/lib/yit-plugin-panel.php
		 */
		public function add_ywcfav_menu() {
			if ( ! empty( $this->panel ) ) {
				return;
			}

			$admin_tabs = apply_filters(
				'ywcfav_add_premium_tab',
				array(
					'video-settings' => __( 'Video Settings', 'yith-woocommerce-featured-video' ),
					'premium'        => __( 'Premium Version', 'yith-woocommerce-featured-video' ),
				)
			);

			$args = array(
				'create_menu_page' => true,
				'parent_slug'      => '',
				'page_title'       => 'YITH WooCommerce Featured Audio & Video Content',
				'plugin_slug'      => YWCFAV_SLUG,
				'menu_title'       => 'Featured Audio & Video Content',
				'capability'       => 'manage_options',
				'parent'           => '',
				'class'            => yith_set_wrapper_class(),
				'parent_page'      => 'yith_plugin_panel',
				'page'             => $this->panel_page,
				'admin-tabs'       => $admin_tabs,
				'options-path'     => YWCFAV_DIR . '/plugin-options',
				'is_premium'       => true,
			);

			$this->panel = new YIT_Plugin_Panel_WooCommerce( $args );
		}


		/**
		 * Show custom metabox into product settings
		 *
		 * @since 2.0.0
		 */
		public function add_video_field() {
			$args = apply_filters(
				'ywcfav_simple_url_video_args',
				array(
					'id'          => '_video_url',
					'label'       => __( 'Featured Video URL', 'yith-woocommerce-featured-video' ),
					'placeholder' => __( 'Video URL', 'yith-woocommerce-featured-video' ),
					'desc_tip'    => true,
					'description' => sprintf( __( 'Enter the URL for the video you want to show in place of the featured image in the product detail page. (the services enabled are: YouTube and Vimeo ).', 'yith-woocommerce-featured-video' ) ),
				)
			);

			wc_get_template( 'admin/add_simple_url_video.php', $args, '', YWCFAV_TEMPLATE_PATH );
		}

		/**
		 * Set_custom_product_meta
		 *
		 * @param WC_Product $product product.
		 *
		 * @since 2.0.0
		 */
		public function set_custom_product_meta( $product ) {

			$video_url     = isset( $_POST['_video_url'] ) ? wp_unslash( $_POST['_video_url'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$old_value_url = $product->get_meta( '_video_url' );

			if ( $video_url !== $old_value_url ) {
				$product->update_meta_data( '_video_url', $video_url );
				$img_id = '';
				if ( ! empty( $video_url ) ) {
					$video_info = explode( ':', ywcfav_video_type_by_url( $video_url ) );
					$img_id     = $this->save_video_thumbnail(
						array(
							'host' => $video_info[0],
							'id'   => $video_info[1],
						)
					);
				}
				$product->update_meta_data( '_video_image_url', $img_id );
			}

		}

		/**
		 * Save_video_thumbnail
		 *
		 * @param array $video_info video info.
		 */
		public function save_video_thumbnail( $video_info ) {

			$name   = isset( $video_info['name'] ) ? $video_info['name'] : $video_info['id'];
			$result = 'no';
			switch ( $video_info['host'] ) {

				case 'vimeo':
					if ( ! empty( $video_info['id'] ) ) {
						$img_url = 'https://vumbnail.com/' . $video_info['id'] . '.jpg';

						if ( ! empty( $img_url ) ) {
							$result = 'ok';
						}
					}
					break;
				case 'youtube':
					$youtube_image_sizes = array(
						'maxresdefault',
						'hqdefault',
						'mqdefault',
						'sqdefault',
					);

					$youtube_url = 'https://img.youtube.com/vi/' . $video_info['id'] . '/';
					$result      = 'no';
					foreach ( $youtube_image_sizes as $image_size ) {

						$img_url      = $youtube_url . $image_size . '.jpg';
						$get_response = wp_remote_get( $img_url );
						if ( ! is_wp_error( $get_response ) ) {
							$result = 200 === $get_response['response']['code'] ? 'ok' : 'no';
						}
						if ( 'ok' === $result ) {
							break;
						}
					}

					break;
			}

			$img_id = '';

			if ( 'ok' === $result ) {

				$img_id = ywcfav_save_remote_image( $img_url, $name );
			} else {
				$img_id = get_option( 'ywcfav_video_placeholder_id' );
			}

			return $img_id;
		}

		/**
		 * Save_video_placeholder
		 *
		 * @return void
		 */
		public function save_video_placeholder() {

			if ( apply_filters( 'ywcfav_generate_video_placeholder', true ) ) {
				$video_id  = get_option( 'ywcfav_video_placeholder_id', false );
				$video_src = false;

				if ( false !== $video_id ) {
					$video_src = wp_get_attachment_image_src( $video_id );
				}

				if ( false === $video_src ) {

					$video_id = ywcfav_save_remote_image( YWCFAV_ASSETS_URL . 'images/videoplaceholder.jpg', 'videoplaceholder' );

					update_option( 'ywcfav_video_placeholder_id', $video_id );
				}
			}
		}

	}
}

if ( ! function_exists( 'YITH_Featured_Audio_Video_Admin' ) ) {

	/**
	 * YITH_Featured_Audio_Video_Admin
	 *
	 * @return YITH_Featured_Audio_Video_Admin|YITH_Featured_Audio_Video_Admin_Premium
	 */
	function YITH_Featured_Audio_Video_Admin() { // phpcs:ignore WordPress.NamingConventions
		$instance = null;
		if ( class_exists( 'YITH_Featured_Audio_Video_Admin_Premium' ) ) {
			$instance = YITH_Featured_Audio_Video_Admin_Premium::get_instance();
		} else {
			$instance = YITH_Featured_Audio_Video_Admin::get_instance();
		}

		return $instance;
	}
}
