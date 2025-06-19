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


$variation_id = $variation->ID;

$video_params  = get_post_meta( $variation_id, '_ywcfav_variation_video', true );
$show_form_add = ! empty( $video_params ) ? 'display:none;' : 'display:block;';

?>
<div class="ywcfav_variable_video" style="margin-top: 25px;">

<div class="ywcfav_variable_video_container" data-loop="<?php echo esc_attr( $loop ); ?>">
	<?php
	if ( ! empty( $video_params ) ) :
		$args = array(
			'video_params' => $video_params,
			'loop'         => $loop,
			'product_id'   => $variation_id,
		);
		wc_get_template( 'metaboxes/views/html-product-variation-video.php', $args, '', YWCFAV_TEMPLATE_PATH );
	endif;
	?>
</div>
	<div class="ywcfav_variable_video_add_container" style="<?php echo esc_attr( $show_form_add ); ?>">
		<p class="form-row form-row-full">
			<label><?php esc_html_e( 'Add Video', 'yith-woocommerce-featured-video' ); ?></label>
			<select class="ywcfav_variation_video_add_by">
				<option value=""><?php esc_html_e( 'Select By...', 'yith-woocommerce-featured-video' ); ?></option>
				<option value="id"><?php esc_html_e( 'By ID', 'yith-woocommerce-featured-video' ); ?></option>
				<option value="url"><?php esc_html_e( 'By URL', 'yith-woocommerce-featured-video' ); ?></option>
				<option value="embd"><?php esc_html_e( 'By Embedded code', 'yith-woocommerce-featured-video' ); ?></option>
				<option value="upload"><?php esc_html_e( 'By Upload', 'yith-woocommerce-featured-video' ); ?></option>
			</select>
		</p>
		<p class="variation_video_title form-row form-row-full" style="display: none;">
			<label><?php esc_html_e( 'Video Title', 'yith-woocommerce-featured-video' ); ?></label>
			<input type="text" class="ywcfav_variation_video_title" />
		</p>
		<p class="variation_video_add_by_id form-row form-row-first" style="display: none;">
			<label><?php esc_html_e( 'Video ID', 'yith-woocommerce-featured-video' ); ?><a class="tips" data-tip="<?php esc_html_e( 'YouTube and Vimeo are supported', 'yith-woocommerce-featured-video' ); ?>" href="#">[?]</a></label>
			<input type="text" class="ywcfav_variation_video_add_by_id" />
		</p>

		<p class="variation_video_add_by_url form-row form-row-first" style="display: none;">
			<label><?php esc_html_e( 'Video URL', 'yith-woocommerce-featured-video' ); ?> <a class="tips" data-tip="<?php esc_html_e( 'YouTube and Vimeo are supported', 'yith-woocommerce-featured-video' ); ?>" href="#">[?]</a></label>
			<input type="text" class="ywcfav_variation_video_add_by_url" />
		</p>
		<p class="variation_video_add_by_embedded form-row form-row-full" style="display: none;">
			<label><?php esc_html_e( 'Embedded code', 'yith-woocommerce-featured-video' ); ?></label>
			<textarea class="ywcfav_variation_video_add_by_embedded" style="width: 100%;" rows="4"></textarea>
		</p>

		<p class="variation_video_type form-row form-row-last" style="display: none;">
			<label for="ywcfav_variation_video_type_host"><?php esc_html_e( 'Host', 'yith-woocommerce-featured-video' ); ?></label>
			<select class="ywcfav_variation_video_type_host">
				<option value="youtube"><?php esc_html_e( 'YouTube', 'yith-woocommerce-featured-video' ); ?></option>
				<option value="vimeo"><?php esc_html_e( 'Vimeo', 'yith-woocommerce-featured-video' ); ?></option>
			</select>
		</p>
				<p class="variation_video_button_add form-row form-row-first" style="display: none;">
					<button type="button" class="button button-primary ywcfav_add_variable_video"><?php esc_html_e( 'Add Video', 'yith-woocommerce-featured-video' ); ?></button>
				</p>
		<p class="variation_video_add_by_upload form-row form-row-first" style="display: none;">
			<button type="button" class="ywcfav_variation_video_add_by_upload button button-primary" data-choose="<?php esc_html_e( 'Select Video', 'yith-woocommerce-featured-video' ); ?>"><?php esc_html_e( 'Upload', 'yith-woocommerce-featured-video' ); ?></button>
			<input type="hidden" name="ywcfav_variation_video_id_up" value="" class="ywcfav_variation_video_id" />
		</p>
	</div>
</div>
