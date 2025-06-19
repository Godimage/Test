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
	exit; // Exit if accessed directly!
}

global $product;

$show_gallery_in_sidebar = 'yes' === get_option( 'ywcfav_show_gallery_in_sidebar' );

if ( $show_gallery_in_sidebar ) {
	$columns = 1;

} else {
	$columns = apply_filters( 'woocommerce_product_thumbnails_columns', 3 );

}
$type_link = ywcfav_check_is_zoom_magnifier_is_active() && 'yes' === get_option( 'ywcfav_zoom_magnifer_option' ) ? 'zoom_mode' : 'modal_mode';
$video_txt = apply_filters( 'yith_ywcfav_video_slider_name', _n( 'Featured Video', 'Featured Videos', count( $all_video ), 'yith-woocommerce-featured-video' ) );
if ( ! empty( $all_video ) ) :?>

	<div class="ywcfav_thumbnails_video_container">
		<div class="ywcfav_slider_info">
			<div class="ywcfav_slider_name"><?php echo esc_html( $video_txt ); ?></div>
			<div class="ywcfav_slider_control">
				<div class="ywcfav_left"></div>
				<div class="ywcfav_right"></div>
			</div>
		</div>
		<div class="ywcfav_slider_wrapper">
			<div class="ywcfav_slider">
				<div class="ywcfav_slider_video owl-carousel owl-theme" data-n_columns="<?php echo esc_attr( $columns ); ?>">
					<?php
					$i                 = 0;
					$gallery_thumbnail = wc_get_image_size( 'gallery_thumbnail' );

					$thumbnail_size = apply_filters(
						'woocommerce_gallery_thumbnail_size',
						array(
							$gallery_thumbnail['width'],
							$gallery_thumbnail['height'],
						)
					);
					foreach ( $all_video as $video ) :


						$args_url = array(
							'action'     => 'print_modal',
							'product_id' => $product->get_id(),
							'content_id' => $video['id'],

						);

						$terms_url     = esc_url( add_query_arg( $args_url, admin_url( 'admin-ajax.php' ) ) );
						$thumbnail_id  = $video['thumbn'];
						$thumbnail_url = wp_get_attachment_image_src( $thumbnail_id, $thumbnail_size );
						$thumbnail_url = isset( $thumbnail_url[0] ) ? $thumbnail_url[0] : '';


						$a = sprintf(
							'<a href="%s" class="%s" data-type="ajax" rel="nofollow" title="%s"><img src="%s" alt="%s"></a>',
							$terms_url,
							'ywcfav_show_modal',
							$video['name'],
							$thumbnail_url,
							$video['name']
						);

						$a = apply_filters( 'ywcfav_gallery_modal_link', $a, 'video', $terms_url, $video, $thumbnail_url, $product );
						?>
						<div id="<?php echo esc_attr( $video['id'] ); ?>"
							class=" ywcfav_video_modal_container">
							<?php echo $a; //phpcs:ignore WordPress.Security.EscapeOutput ?>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
	</div>
<?php endif; ?>
