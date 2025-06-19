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
	jQuery(function($) {
		var iframe_id = '<?php echo esc_html( $id ); ?>',
			iframe = $(document).find('#'+iframe_id),
			global_args = iframe.data('video'),
			vimeo_data = iframe.data('vimeo'),
			volume = global_args.volume,
			player = new Vimeo.Player(iframe_id, vimeo_data),
			force_stop= false;

		var hide_gallery_trigger_and_onsale_icon = function (e) {
				jQuery('.woocommerce span.onsale:first, .woocommerce-page span.onsale:first').hide();

			},
			show_gallery_trigger_and_onsale_icon = function (e) {
				jQuery('.woocommerce span.onsale:first, .woocommerce-page span.onsale:first').show();

			};


		player.setVolume(volume);
		player.on('play', function () {
			hide_gallery_trigger_and_onsale_icon();

		});
		player.on('pause', function () {
			var is_stoppable = 'yes' == global_args.is_stoppable;
			if (!is_stoppable && !force_stop) {
				player.play();
				force_stop = false;
			}
			show_gallery_trigger_and_onsale_icon();

		});


		jQuery(document).trigger('ywfav_custom_content_created', [player,iframe]);

	});

</script>
