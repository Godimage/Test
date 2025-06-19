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

	function loadScript() {
		if (typeof (YT) == 'undefined' || typeof (YT.Player) == 'undefined') {
			var tag = document.createElement('script');
			tag.src = "https://www.youtube.com/iframe_api";
			var firstScriptTag = document.getElementsByTagName('script')[0];
			firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
		}
	}

	function loadPlayer() {
		window.onYouTubePlayerAPIReady = function () {
			onYouTubeIframeAPIReady();
		};
	}


	jQuery(function () {
		loadScript();
		loadPlayer();
	});

	var iframe_id = '<?php echo esc_html( $id ); ?>',
		iframe = jQuery(document).find('#' + iframe_id),
		ytb_data = iframe.data('ytb'),
		global_args = iframe.data('video'),
		force_stop = false,
		player = null;
	// 3. This function creates an <iframe> (and YouTube player)
	//    after the API code downloads.
	var hide_gallery_trigger_and_onsale_icon = function (e) {
			jQuery('.woocommerce span.onsale:first, .woocommerce-page span.onsale:first').hide();
			var target = jQuery(e.target.h),
			parent = target.parents('.yith-quick-view-content');


			if( parent.length ){
				parent.find('.yith-quick-view-thumbs').hide();
			}
		},
		show_gallery_trigger_and_onsale_icon = function (e) {
			jQuery('.woocommerce span.onsale:first, .woocommerce-page span.onsale:first').show();
			var target = jQuery(e.target.h),
			parent = target.parents('.yith-quick-view-content');


			if( parent.length ){
				parent.find('.yith-quick-view-thumbs').show();
			}
		};

	function onYouTubeIframeAPIReady() {
		player = new YT.Player(iframe_id, {
			videoId: ytb_data.videoId,
			playerVars: ytb_data,
			events: {
				'onReady': onPlayerReady,
				'onStateChange': onPlayerStateChange
			}
		});


	}

	function onPlayerReady(event) {

		var volume = global_args.volume * 100;
		event.target.setVolume(volume);
		if (ytb_data.autoplay == 1) {
			<?php if ( apply_filters( 'ywfav_mute_video_on_autoplay', true ) ) : ?>
			event.target.mute();
			<?php endif; ?>
			event.target.playVideo();
		}
		
		jQuery(document).trigger('ywfav_custom_content_created', [event.target, event.target.h]);
	}

	function onPlayerStateChange(event) {

		if (event.data === YT.PlayerState.PAUSED) {

			var can_stop_video = global_args.is_stoppable;

			if ('no' === can_stop_video && !force_stop) {
				event.target.playVideo();
				force_stop = false;
			}
			var iframe = player.a;
			jQuery(iframe).toggleClass('ywfav_playing');
			show_gallery_trigger_and_onsale_icon(event);

		} else if (event.data === YT.PlayerState.ENDED) {
			var iframe = player.a;
			jQuery(iframe).toggleClass('ywfav_playing');
			show_gallery_trigger_and_onsale_icon(event);

		} else if (event.data === YT.PlayerState.PLAYING) {

			var iframe = player.a;
			jQuery(iframe).toggleClass('ywfav_playing');
			hide_gallery_trigger_and_onsale_icon(event);
		}
	}
</script>
