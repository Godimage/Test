<?php
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

global $post, $product_object;

$product_id = ! empty( $product_object ) && is_callable( array( $product_object, 'get_id' ) ) ? $product_object->get_id() : $post->ID;

$product_audio = get_post_meta( $product_id, '_ywcfav_audio', true );


$data_tip = sprintf(
	'%s <a target="_blank" href="http://soundcloud.com/">%s </a> %s',
	__( 'Insert the', 'yith-woocommerce-featured-video' ),
	__( 'SoundCloud.com', 'yith-woocommerce-featured-video' ),
	__( 'song URL', 'yith-woocommerce-featured-video' )
);
?>
<div id="ywcfav_audio_data" class="panel wc-metaboxes-wrapper">
	<div class="toolbar toolbar-top">
		<span class="expand-close">
			<a href="#" class="expand_all"><?php esc_html_e( 'Expand', 'woocommerce' ); ?></a> / <a href="#" class="close_all"><?php esc_html_e( 'Close', 'woocommerce' ); ?></a>
		</span>
		<strong><?php esc_html_e( 'Product Audio', 'yith-woocommerce-featured-video' ); ?></strong>
	</div>
	<div class="product_audio wc-metaboxes">
		<?php
		if ( ! empty( $product_audio ) ) {

			$loop = 0;
			foreach ( $product_audio as $audio ) {

				$args = array(
					'audio_params' => $audio,
					'loop'         => $loop,
					'product_id'   => $product_id,
				);

				wc_get_template( 'metaboxes/views/html-product-audio.php', $args, '', YWCFAV_TEMPLATE_PATH );
				$loop++;
			}
		}
		?>
	</div>
	<div id="ywcfav_form_audio_container">

		<div class="ywcfav_audio_single_container" >
			<p class="form-field">
				<span class="audio_name">
					<label for="ywcfav_audio_name"><?php esc_html_e( 'Audio Name', 'yith-woocommerce-featured-video' ); ?></label>
					<input type="text" id="ywcfav_audio_name" />
				</span>
			</p>
			<p class="form-field">
				<span class="audio_url">
					<label for="ywcfav_audio_url"><?php esc_html_e( 'Audio URL', 'yith-woocommerce-featured-video' ); ?>&nbsp;<a class="tips" data-tip='<?php esc_attr_e( $data_tip ); //phpcs:ignore?>' href="#">[?]</a></label> 
					<input type="text" id="ywcfav_audio_url" />
				</span>
				<span class="audio_button">
					<button type="button" class="button button-primary ywcfav_insert_track"><?php esc_html_e( 'Add Audio Track', 'yith-woocommerce-featured-video' ); ?></button>
				</span>
			</p>
		</div>
	</div>
	<?php do_action( 'ywcfav_woocommerce_product_options_audios' ); ?>

</div>
