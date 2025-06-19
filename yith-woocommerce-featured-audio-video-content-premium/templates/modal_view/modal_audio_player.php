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


$query_args = array(
	'url'          => $audio_url,
	'auto_play'    => 'yes' === $autoplay ? 'true' : 'false',
	'show_artwork' => 'yes' === $show_artwork ? 'true' : 'false',
	'sharing'      => 'yes' === $show_sharing ? 'true' : 'false',
	'color'        => str_replace( '#', '', $color ),


);

$audio_args = array(
	'volume' => $volume * 100,
);

$id = $id . '_modal'; // phpcs:ignore
$audio_args     = htmlspecialchars( wp_json_encode( $audio_args ) );
$url            = add_query_arg( $query_args, 'https://w.soundcloud.com/player/' );
?>
<div class="ywcfav-audio-content">
	<iframe id="<?php echo esc_attr( $id ); ?>" src="<?php echo esc_attr( $url ); ?>" frameborder="no" scrolling="no"
			data-audio="<?php echo esc_attr( $audio_args ); ?>"></iframe>
	<?php
	require_once YWCFAV_DIR . 'assets/php/audio_manager.php';
	?>
</div>
