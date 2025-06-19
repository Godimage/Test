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
wp_enqueue_script( 'videojs' );
?>
<script type="text/javascript">
	jQuery(function ($) {

		var iframe_id = '<?php echo esc_html( $id ); ?>',
			iframe = null,
			force_stop = false,
			player = null;

		var hide_gallery_trigger_and_onsale_icon = function (e) {
				jQuery('.woocommerce span.onsale:first, .woocommerce-page span.onsale:first').hide();

			},
			show_gallery_trigger_and_onsale_icon = function (e) {
				jQuery('.woocommerce span.onsale:first, .woocommerce-page span.onsale:first').show();

			};
		if( !$(document).find('.vbox-inline .ywcfav-video-content.host' ).length ){
			iframe_id = iframe_id+'_html5_api';
		}

		iframe_id = iframe_id.replace('_html5_api','');
		iframe = $(document).find('#'+iframe_id);

		if (iframe.length) {
			global_args = iframe.data('setup'),
				is_stoppable = false;
			if (typeof global_args !== 'undefined') {
				is_stoppable = 'yes' == global_args.is_stoppable;
			}

			if (typeof videojs !== 'undefined') {
				player = videojs(iframe_id);

				player.ready(function () {

					setTimeout(function () {
						if (global_args.autoplay && global_args.featured_content_selected && !force_stop) {
						// player.autoplay('muted');

						}
					}, 1000);
					player.volume(global_args.volume);
					$(document).trigger('ywfav_custom_content_created', [player, iframe]);

					this.on('pause', function () {

						if (!is_stoppable && !this.ended() && !force_stop) {
							player.play();
							force_stop = false;
						}
						show_gallery_trigger_and_onsale_icon(false);
					});

					this.on('ended', function () {
						force_stop =true;
						show_gallery_trigger_and_onsale_icon(event);
					});

					this.on('playing', function () {

						hide_gallery_trigger_and_onsale_icon(event);
					});
				});
			}

		}
	});
</script>
