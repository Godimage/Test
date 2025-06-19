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


$args_url = array(
	'action'     => 'print_modal',
	'product_id' => $args['product_id'],
	'content_id' => $args['id'],
);


$terms_url          = esc_url( add_query_arg( $args_url, admin_url( 'admin-ajax.php' ) ) );
$gallery_item_class = ywcfav_get_gallery_item_class();

$gallery_thumbnail = wc_get_image_size( 'gallery_thumbnail' );

$thumbnail_size = apply_filters(
	'woocommerce_gallery_thumbnail_size',
	array(
		$gallery_thumbnail['width'],
		$gallery_thumbnail['height'],
	)
);

$thumbnail_id = $args['thumbnail_id'];

$thumbnail_url = wp_get_attachment_image_src( $thumbnail_id, $thumbnail_size );

$thumbnail_url = isset( $thumbnail_url[0] ) ? $thumbnail_url[0] : '';
$image_size    = apply_filters( 'woocommerce_gallery_image_size', 'woocommerce_single' );

$image_full_src = wp_get_attachment_image_src( $thumbnail_id, 'full' );
$image          = wp_get_attachment_image(
	$thumbnail_id,
	$image_size,
	false,
	array(
		'title'                   => get_post_field( 'post_title', $thumbnail_id ),
		'data-caption'            => get_post_field( 'post_excerpt', $thumbnail_id ),
		'data-src'                => $image_full_src[0],
		'data-large_image'        => $image_full_src[0],
		'data-large_image_width'  => $image_full_src[1],
		'data-large_image_height' => $image_full_src[2],
		'class'                   => 'wp-post-image skip-lazy',
	)
);

$data = htmlspecialchars( wp_json_encode( $args ) );
?>

<div id="<?php echo esc_attr( $args['id'] ); ?>" class="yith_featured_content ywcfav_video_modal_container <?php echo esc_attr( $gallery_item_class ); ?>" data-thumb="<?php echo esc_attr( $thumbnail_url ); ?>">
	<a class="ywcfav_show_modal" data-type="ajax" rel="nofollow" href="<?php echo esc_attr( $terms_url ); ?>">
		<?php echo $image; //phpcs:ignore WordPress.Security.EscapeOutput ?>
	</a>
</div>
