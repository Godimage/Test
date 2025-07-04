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
?>
<div class="ywcfav_video_embedded_iframe" id="<?php echo esc_attr( $id ); ?>">
	<?php echo urldecode( $video_id ); //phpcs:ignore WordPress.Security.EscapeOutput ?>
</div>
