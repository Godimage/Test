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
<script type="text/javascript">
	jQuery(function($){

		var iframe_id = '<?php echo esc_html( $id ); ?>',
			iframe = $(document).find('#'+iframe_id),
			audio_args = iframe.data('audio');

		if (typeof SC !== 'undefined') {

		var  player = SC.Widget(iframe_id);

			player.bind(SC.Widget.Events.READY, function () {

				iframe.css({'width': '100%'});
				player.setVolume(audio_args.volume);

				jQuery(document).trigger('ywfav_custom_content_created', [player,iframe]);
			});

			player.bind(SC.Widget.Events.PLAY, function () {

				hide_gallery_trigger_and_onsale_icon(false);

			});
			player.bind(SC.Widget.Events.FINISH, function () {

				show_gallery_trigger_and_onsale_icon(false);
			});
			player.bind(SC.Widget.Events.PAUSE, function () {

				show_gallery_trigger_and_onsale_icon(false);
			});
		}
	});
</script>
