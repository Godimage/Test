<?php // phpcs:ignore WordPress.NamingConventions
/**
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @package YITH\WooCommerceFeaturedAudioandVideoContent
 */

$url = 'https://www.youtube.com/embed/' . $video_id;

$args = array(
	'enablejsapi' => '1',
	'origin'      => get_site_url(),
	'rel'         => 'yes' === $show_rel ? 1 : 0,
	'autoplay'    => 'yes' === $autoplay ? 1 : 0,

);

if ( 'yes' === $loop ) {
	$args['loop']     = 1;
	$args['playlist'] = $video_id;
}


$url = esc_url( add_query_arg( $args, $url ) );
?>
<iframe id="<?php echo esc_attr( $id ); ?>" src="<?php echo esc_attr( $url ); ?>" data-video="<?php echo esc_attr( $video_data ); ?>"
		data-ytb="<?php echo esc_attr( $data ); ?>" frameborder="0" scrolling="no" allowfullscreen allow="autoplay; accelerometer; encrypted-media; gyroscope; picture-in-picture">
</iframe>
<?php
require YWCFAV_DIR . 'assets/php/youtube_manager.php';
?>
