<?php
defined( 'ABSPATH' ) || exit;
global $product;

$video_id = get_post_meta( $product->get_id(), 'fpgv_video_id', true );
$thumb_id = get_post_meta( $product->get_id(), 'fpgv_thumb_id', true );
$auto     = get_post_meta( $product->get_id(), 'fpgv_auto_resize', true ) === 'yes';

$video_html = '';
if ( $video_id ) {
    $video_url = wp_get_attachment_url( $video_id );
    $thumb_url = $thumb_id ? wp_get_attachment_url( $thumb_id ) : '';
    $style     = $auto ? 'style="max-width:100%; height:auto;"' : '';

    $video_html = sprintf(
        '<div class="ux-slider-slide fpgv-video-slide"><video controls %s poster="%s"><source src="%s" type="video/%s"></video></div>',
        $style,
        esc_url( $thumb_url ),
        esc_url( $video_url ),
        esc_attr( pathinfo( $video_url, PATHINFO_EXTENSION ) )
    );
}

?>
<div class="product-gallery ux-slider">
  <?php
    echo $video_html;
    do_action( 'woocommerce_product_thumbnails' );
  ?>
</div>
