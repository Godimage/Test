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

$audio_label = empty( $audio_params['name'] ) ? 'Audio ' . $loop : $audio_params['name'];
$thumbnail   = $audio_params['thumbn'];

if ( is_numeric( $thumbnail ) ) {
	$url = wp_get_attachment_image_src( $thumbnail, 'full' );
	$url = $url[0];

} else {
	$url = $thumbnail;
}


$featured_content_meta = get_post_meta( $product_id, '_ywcfav_featured_content', true );

$is_featured    = ( isset( $featured_content_meta['id'] ) && $featured_content_meta['id'] === $audio_params['id'] );
$featured_label = $is_featured ? '( ' . __( 'Featured', 'yith-woocommerce-featured-video' ) . ' )' : '';

?>
<div class="ywcfav_woocommerce_audio wc-metabox closed">
	<h3>
		<a href="#" class="ywcfav_delete_audio delete"><?php esc_html_e( 'Remove', 'woocommerce' ); ?></a>
		<div class="handlediv" title="<?php esc_attr_e( 'Click to toggle', 'woocommerce' ); ?>"></div>
		<strong class="attribute_name"><?php echo esc_html( $audio_label ); ?></strong>
		<span class="label_is_featured"><?php echo esc_html( $featured_label ); ?></span>
	</h3>
	<div class="ywcfav_woocommerce_audio_data wc-metabox-content">
		<table cellpadding="0" cellspacing="0">
			<tbody>
			<tr>
				<td class="ywcfav_audio_image">
					<label><?php esc_html_e( 'Audio Thumbnail:', 'yith-woocommerce-featured-video' ); ?></label>
					<a href="#" class="ywcfav_upload_image_button" data-choose="<?php esc_attr_e( 'Select Thumbnail', 'yith-woocommerce-featured-video' ); ?>" >
						<img src="<?php echo esc_attr( $url ); ?>" class="ywcfav_thumbn upload_image_id"/>
					</a>
					<input type="hidden" class="thumbn_id" name="ywcfav_audio[<?php echo esc_attr( $loop ); ?>][thumbn]" value="<?php echo esc_attr( $thumbnail ); ?>" />
					<input type="hidden" class="ywcfav_audio_id" name="ywcfav_audio[<?php echo esc_attr( $loop ); ?>][id]" value="<?php echo esc_attr( $audio_params['id'] ); ?>" />
					<input type="hidden" class="ywcfav_audio_id" name="ywcfav_audio[<?php echo esc_attr( $loop ); ?>][featured]" value="<?php echo esc_attr( $is_featured ) ? 'featured' : 'no'; ?>"/>
				</td>

			</tr>
			<tr class="ywcfav_extra_info">
				<td class="ywcfav_audio_title">
					<label><?php esc_html_e( 'Audio Title', 'yith-woocommerce-featured-video' ); ?></label>
					<input type="text" name="ywcfav_audio[<?php echo esc_attr( $loop ); ?>][name]" value="<?php echo esc_attr( $audio_label ); ?>" />
				</td>
				<td class="ywcfav_audio_url">
						<label><?php esc_html_e( 'Audio Url', 'yith-woocommerce-featured-video' ); ?></label>
						<span class="ywcfav_audio_content_label"><?php echo esc_attr( $audio_params['url'] ); ?></span>
						<input type="hidden" class="ywcfav_audio_url" name="ywcfav_audio[<?php echo esc_attr( $loop ); ?>][url]" value="<?php echo esc_attr( $audio_params['url'] ); ?>">
					</div>
				</td>

			</tr>
			</tbody>
		</table>
	</div>
</div>
