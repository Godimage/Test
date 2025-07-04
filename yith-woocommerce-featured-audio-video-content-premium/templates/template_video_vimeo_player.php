<?php // phpcs:ignore WordPress.NamingConventions
/**
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @package YITH\WooCommerceFeaturedAudioandVideoContent
 */

?>

<div id="<?php echo esc_attr( $id ); ?>" data-video="<?php echo esc_attr( $video_data ); ?>" data-vimeo="<?php echo esc_attr( $data ); ?>" class="iframe">
</div>
<?php

require YWCFAV_DIR . 'assets/php/vimeo_manager.php';
?>
