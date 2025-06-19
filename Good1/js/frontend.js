/**
 * Frontend JavaScript for Flatsome Product Gallery Video.
 *
 * This script handles the click event on the video thumbnail in the WooCommerce
 * product gallery, replacing the main product image with a video player.
 *
 * @version 2.0.0
 */
jQuery( document ).ready( function( $ ) {

	'use strict';

	/**
	 * Selector for the video thumbnail link.
	 * Targets anchor tags within the gallery slider that have the specific video URL data attribute.
	 * @type {string}
	 */
	const videoThumbnailSelector = '.product-gallery-slider .slide a[data-fpgv-video-url]';

	/**
	 * Binds a click event listener to the document, delegating to the video thumbnail.
	 * This approach is robust and works even if the gallery is loaded via AJAX.
	 */
	$( document ).on( 'click', videoThumbnailSelector, function( e ) {

		// 1. Prevent the default link action and stop Flatsome's lightbox.
		e.preventDefault();
		e.stopImmediatePropagation();

		const $thumbnailLink = $( this );

		// 2. Retrieve video data from the thumbnail's data attributes.
		const videoUrl = $thumbnailLink.data( 'fpgv-video-url' );
		const autoResize = $thumbnailLink.data( 'fpgv-auto-resize' );

		// Exit if the video URL is not found.
		if ( ! videoUrl ) {
			console.error( 'FPGV Error: Video URL not found on the clicked thumbnail.' );
			return;
		}

		// 3. Identify the main image container in the Flatsome gallery.
		// This is typically the very first slide in the main gallery slider.
		const $mainImageContainer = $( '.woocommerce-product-gallery__wrapper .flickity-slider .slide:first-child' );

		if ( $mainImageContainer.length === 0 ) {
			console.error( 'FPGV Error: Main product image container could not be found.' );
			return;
		}

		// 4. Construct the responsive video player element.
		// `autoplay`, `muted`, and `playsinline` are crucial for a seamless UX on all devices.
		const $videoPlayer = $( '<video>', {
			src: videoUrl,
			class: 'fpgv-video-player',
			controls: true,
			autoplay: true,
			muted: true,
			playsinline: true,
			loop: true, // Looping is good for short, demonstrative product videos.
		} );

		// 5. Apply the auto-resize class if the setting is enabled.
		// CSS will handle the actual resizing to match the gallery dimensions.
		if ( autoResize === 'yes' ) {
			$videoPlayer.addClass( 'fpgv-auto-resize' );
		}

		// 6. Inject the video player into the main image container, replacing the existing image.
		$mainImageContainer.html( $videoPlayer );

		// 7. Ensure the main gallery slider is positioned on the first slide (where the video now is).
		// This is important because clicking a thumbnail might have changed the slider's position.
		const $gallery = $( '.woocommerce-product-gallery' ).first();
		if ( $gallery.length && typeof $gallery.data( 'flickity' ) !== 'undefined' ) {
			// Select the first slide (index 0) instantly (isWrapped = true) without animation.
			$gallery.flickity( 'select', 0, true );
		}
	} );

} );
