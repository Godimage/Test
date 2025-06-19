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

$video_type       = strtolower( $video_params['type'] );
$video_host_label = ( isset( $video_params['host'] ) && ! empty( $video_params['host'] ) ) ? $video_params['host'] : __( 'None', 'yith-woocommerce-featured-video' );
?>

<div class="img_field">
		<p class="form-row form-row-first upload_image">
			<label><?php esc_html_e( 'Featured Video', 'yith-woocommerce-featured-video' ); ?></label>
			<a href="#" class="ywcfav_variation_upload_image_button" data-choose="<?php esc_html_e( 'Select Thumbnail', 'yith-woocommerce-featured-video' ); ?>" >
				<img src="<?php echo esc_attr( $url ); ?>" class="ywcfav_variation_thumbn upload_image_id"/>

				<input type="hidden" class="thumbn_id " name="video_info[<?php echo esc_attr( $loop ); ?>][thumbn]" value="<?php echo esc_attr( $thumbnail ); ?>" />
				<input type="hidden" class="video_type" name="video_info[<?php echo esc_attr( $loop ); ?>][type]" value="<?php echo esc_attr( $video_params['type'] ); ?>" />
				<input type="hidden" class="video_content" name="video_info[<?php echo esc_attr( $loop ); ?>][content]" value="<?php echo esc_attr( $video_params['content'] ); ?>" />
				<input type="hidden" class="video_id"      name="video_info[<?php echo esc_attr( $loop ); ?>][id]" value="<?php echo esc_attr( $video_params['id'] ); ?>"/>
			</a>
		</p>

</div>
<div class="variation_video_title">
	<p class="form-row form-row-full video_variation_title">
		<label><?php esc_html_e( 'Video Title', 'yith-woocommerce-featured-video' ); ?></label>
		<input type="text" class="ywcfav_variation_video_title" name="video_info[<?php echo esc_attr( $loop ); ?>][name]" value="<?php echo esc_attr( $video_params['name'] ); ?>" />
	</p>
</div>  
<div class="variation_video_extra_info">
<p class="form-row form-row-first">
	<label><?php esc_html_e( 'Video Host:', 'yith-woocommerce-featured-video' ); ?></label>
	<span class="ywcfav_video_host_label <?php echo esc_attr( $video_params['host'] ); ?>"><?php echo esc_attr( $video_host_label ); ?></span>
	<input type="hidden" name="video_info[<?php echo esc_attr( $loop ); ?>][host]" value="<?php echo esc_attr( $video_params['host'] ); ?>" />
</p>
<p class="form-row form-row-last">
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
		// @codingStandardsIgnoreStart
		$video_content    = '';// $video_params['content'] ;
		// @codingStandardsIgnoreEnd
		break;
	case 'upload':
	case '':
		$label_video_type = __( 'Upload', 'yith-woocommerce-featured-video' );
		$video_content    = get_attached_file( $video_params['content'] );
		break;
}

						$label_video_type = sprintf( '%s %s:', __( 'Video', 'yith-woocommerce-featured-video' ), $label_video_type );
?>

						<label><?php echo esc_html( $label_video_type ); ?></label>
						<span class="ywcfav_video_content_label"><?php echo esc_attr( $video_content ); ?></span>
						<input type="hidden" class="ywcfav_video_content_by_<?php echo esc_attr( $video_type ); ?>" name="video_info[<?php echo esc_attr( $loop ); ?>][content]" value="<?php echo esc_attr( $video_params['content'] ); ?>">

</p>
</div>
<p class="for-row form-row-first">
		<button type="button" class="remove_video button button-primary" rel="<?php echo esc_attr( $loop ); ?>"><?php esc_html_e( 'Remove Video', 'yith-woocommerce-featured-video' ); ?></button>
</p>
