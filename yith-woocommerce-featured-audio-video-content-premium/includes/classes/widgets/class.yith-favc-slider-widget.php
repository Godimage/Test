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

if ( ! class_exists( 'YITH_Featured_Audio_Video_Slider_Widget' ) ) {

	/**
	 * YITH_Featured_Audio_Video_Slider_Widget
	 */
	class YITH_Featured_Audio_Video_Slider_Widget extends WP_Widget {


		/**
		 * __construct
		 *
		 * @return void
		 */
		public function __construct() {
			parent::__construct(
				'yith_wc_featured_audio_video',
				__( 'YITH WooCommerce Featured Audio Video - Slider Video', 'yith-woocommerce-featured-video' ),
				array( 'description' => __( 'Show your video or audio content in sidebar!', 'yith-woocommerce-featured-video' ) )
			);
		}

		/**
		 * Show the widget form
		 *
		 * @param array $instance instance.
		 *
		 * @author YITH <plugins@yithemes.com>
		 */
		public function form( $instance ) {

			$default = array(
				'title'           => isset( $instance['title'] ) ? $instance['title'] : '',
				'ywcfav_how_show' => isset( $instance['ywcfav_how_show'] ) ? $instance['ywcfav_how_show'] : 'video',
			);

			$instance = wp_parse_args( $instance, $default );
			?>

			<div class="ywcfav_widget_content">
				<p>
					<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title', 'yith-woocommerce-featured-video' ); ?></label>
					<input type="text" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php esc_attr_e( $instance['title'] ); //phpcs:ignore ?>"/>
				</p>
				<p>
					<label for="<?php echo esc_attr( $this->get_field_id( 'ywcfav_how_show' ) ); ?>"><?php esc_html_e( 'Choose a content to show', 'yith-woocommerce-featured-video' ); ?></label>
					<select id="<?php echo esc_attr( $this->get_field_id( 'ywcfav_how_show' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'ywcfav_how_show' ) ); ?>">
						<option value="video" <?php selected( 'video', $instance['ywcfav_how_show'] ); ?>> <?php esc_html_e( 'Video', 'yith-woocommerce-featured-video' ); ?></option>
						<option value="audio" <?php selected( 'audio', $instance['ywcfav_how_show'] ); ?>> <?php esc_html_e( 'Audio', 'yith-woocommerce-featured-video' ); ?></option>
					</select>
				</p>
			</div>

			<?php
		}

		/**
		 * Update the widget option
		 *
		 * @param array $new_instance new instance.
		 * @param array $old_instance old instance.
		 *
		 * @return array
		 *
		 */
		public function update( $new_instance, $old_instance ) {

			$instance = array();

			$instance['title']           = isset( $new_instance['title'] ) ? $new_instance['title'] : '';
			$instance['ywcfav_how_show'] = isset( $new_instance['ywcfav_how_show'] ) ? $new_instance['ywcfav_how_show'] : 'video';

			return $instance;
		}

		/**
		 * Show the widget in frontend
		 *
		 * @param array $args args.
		 * @param array $instance instance.
		 *
		 */
		public function widget( $args, $instance ) {

			if ( is_product() ) {

				$widget_title = apply_filters( 'widget_title', empty( $instance['title'] ) ? __( 'Videos' ) : $instance['title'], $instance, $this->id_base );
				$how_show     = $instance['ywcfav_how_show'];

				/**
				 * YITH_Featured_Audio_Video
				 *
				 * @var YITH_WC_Audio_Video_Premium $YITH_Featured_Audio_Video
				 */

				$template = '';
				if ( function_exists( 'YITH_Featured_Video_Manager' ) ) {
					if ( 'video' === $how_show ) {

						ob_start();
						YITH_Featured_Video_Manager()->woocommerce_show_product_video_thumbnails();
						$template = ob_get_contents();
						ob_end_clean();
					} else {

						ob_start();
						YITH_Featured_Video_Manager()->woocommerce_show_product_audio_thumbnails();
						$template = ob_get_contents();
						ob_end_clean();
					}

					echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput
					echo $args['before_title']; // phpcs:ignore WordPress.Security.EscapeOutput
					echo $widget_title; // phpcs:ignore WordPress.Security.EscapeOutput
					echo $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput
					echo $template; // phpcs:ignore WordPress.Security.EscapeOutput
					echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput
				}
			}

		}
	}
}
