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

$video_label = empty( $video_params['name'] ) ? 'Video ' . $loop : $video_params['name'];
$thumbnail   = $video_params['thumbn'];


if ( is_numeric( $thumbnail ) ) {

	$url = wp_get_attachment_image_src( $thumbnail, 'full' );
	$url = $url[0];

} else {
	$url = $thumbnail;
}




$video_type = strtolower( $video_params['type'] );

$featured_content_meta = get_post_meta( $product_id, '_ywcfav_featured_content', true );
$is_featured           = ( isset( $featured_content_meta['id'] ) && $featured_content_meta['id'] === $video_params['id'] );
$featured_label        = $is_featured ? '( ' . __( 'Featured', 'yith-woocommerce-featured-video' ) . ' )' : '';

$video_host_label = ( isset( $video_params['host'] ) && ! empty( $video_params['host'] ) ) ? $video_params['host'] : __( 'None', 'yith-woocommerce-featured-video' );

?>
<div class="ywcfav_woocommerce_video wc-metabox closed">
	<h3>
		<a href="#" class="ywcfav_delete delete"><?php esc_html_e( 'Remove', 'woocommerce' ); ?></a>
		<div class="handlediv" title="<?php esc_attr_e( 'Click to toggle', 'woocommerce' ); ?>"></div>
		<strong class="attribute_name"><?php echo esc_html( $video_label ); ?></strong>
		<span class="label_is_featured"><?php echo esc_html( $featured_label ); ?></span>
	</h3>
	<div class="ywcfav_woocommerce_video_data wc-metabox-content">
		<table cellpadding="0" cellspacing="0">
			<tbody>
				<tr>
					<td class="ywcfav_video_image">
						<label><?php esc_html_e( 'Video Thumbnail:', 'yith-woocommerce-featured-video' ); ?></label>
						<a href="#" class="ywcfav_upload_image_button" data-choose="<?php esc_html_e( 'Select Thumbnail', 'yith-woocommerce-featured-video' ); ?>" >
							<img src="<?php echo esc_attr( $url ); ?>" class="ywcfav_thumbn upload_image_id"/>
						</a>
						<input type="hidden" class="thumbn_id" name="ywcfav_video[<?php echo esc_attr( $loop ); ?>][thumbn]" value="<?php echo esc_attr( $thumbnail ); ?>" />
						<input type="hidden" class="ywcfav_video_id" name="ywcfav_video[<?php echo esc_attr( $loop ); ?>][id]" value="<?php echo esc_attr( $video_params['id'] ); ?>" />
						<input type="hidden"  name="ywcfav_video[<?php echo esc_attr( $loop ); ?>][type]" value="<?php echo esc_attr( $video_params['type'] ); ?>" />
						<input type="hidden"  name="ywcfav_video[<?php echo esc_attr( $loop ); ?>][featured]" value="<?php echo $is_featured ? 'featured' : 'no'; ?>" />
					</td>
				</tr>
			<tr class="ywcfav_extra_info">
				<td class="ywcfav_video_title">
					<label><?php esc_html_e( 'Video Title', 'yith-woocommerce-featured-video' ); ?></label>
					<input type="text" name="ywcfav_video[<?php echo esc_attr( $loop ); ?>][name]" value="<?php echo esc_attr( $video_label ); ?>" />
				</td>
				<td class="ywcfav_video_host_type">
					<label><?php esc_html_e( 'Video Host:', 'yith-woocommerce-featured-video' ); ?></label>
					<span class="ywcfav_video_host_label <?php echo esc_attr( $video_params['host'] ); ?>"><?php echo esc_html( $video_host_label ); ?></span>
					<input type="hidden" name="ywcfav_video[<?php echo esc_attr( $loop ); ?>][host]" value="<?php echo esc_attr( $video_params['host'] ); ?>" />
				</td>
				<td class="ywcfav_video_content">
					<div class="ywcfav_video_content_wrap">
						<?php
						$video_content = '';

						switch ( $video_type ) {

							case 'id':
								$label_video_type = __( 'Id', 'yith-woocommerce-featured-video' );
								$video_content    = $video_params['content'];
								break;
							case 'url':
								$label_video_type = __( 'Url', 'yith-woocommerce-featured-video' );
								$video_content    = $video_params['content'];
								break;
							case 'embd':
							case 'embedded':
								$label_video_type = __( 'Embedded Code', 'yith-woocommerce-featured-video' );
								$video_content    = '';
								break;
							case 'upload':
								$label_video_type = __( 'Upload', 'yith-woocommerce-featured-video' );
								$video_content    = get_attached_file( $video_params['content'] );
								break;
						}

						$label_video_type = sprintf( '%s %s:', __( 'Video', 'yith-woocommerce-featured-video' ), $label_video_type );
						?>

						<label><?php echo esc_html( $label_video_type ); ?></label>
						<span class="ywcfav_video_content_label"><?php echo esc_attr( $video_content ); ?></span>
						<input type="hidden" class="ywcfav_video_content_by_<?php echo esc_attr( $video_type ); ?>" name="ywcfav_video[<?php echo esc_attr( $loop ); ?>][content]" value="<?php echo esc_attr( $video_params['content'] ); ?>">
					</div>
				</td>
			</tr>
			</tbody>
		</table>
	</div>
</div>
