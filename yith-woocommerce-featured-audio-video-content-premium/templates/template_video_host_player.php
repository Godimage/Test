<?php // phpcs:ignore WordPress.NamingConventions
/**
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @package YITH\WooCommerceFeaturedAudioandVideoContent
 */

$poster_image = wp_get_attachment_image_src( $thumbnail_id, 'full' );
$poster_image = isset( $poster_image[0] ) ? $poster_image[0] : '';

$query_args['poster'] = $poster_image;
$query_args['fluid']  = true;

$query_args['preload']    = 'auto';
$allow_full_screen        = apply_filters( 'ywcfav_allow_host_video_fullscreen', false );
$query_args['controlBar'] = array( 'fullscreenToggle' => $allow_full_screen );
$data                     = htmlspecialchars( wp_json_encode( $query_args ) );
$src                      = wp_get_attachment_url( $video_id );
$format                   = '';

if ( '' !== $src ) {
	$index  = strlen( $src ) - strrpos( $src, '.' );
	$format = substr( $src, - ( $index - 1 ) );
	if ( 'ogv' === $format ) {
		$format = 'ogg';
	}
}
ob_start();
ywcfav_get_custom_player_style();
$style = ob_get_contents();
ob_end_clean();
echo $style; //phpcs:ignore WordPress.Security.EscapeOutput
?>
<video id="<?php echo esc_attr( $id ); ?>" data-setup="<?php echo esc_attr( $data ); ?>"
	class="video-js vjs-default-skin vjs-default-skin vjs-big-play-centered" muted="muted" playsinline>
	<?php if ( ! empty( $src ) ) : ?>
		<source src="<?php echo esc_attr( $src ); ?>" type="video/<?php echo esc_attr( $format ); ?>"/>
	<?php endif; ?>

</video>
<?php
require YWCFAV_DIR . 'assets/php/host_manager.php';
?>
