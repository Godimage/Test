<?php
/**
 * Plugin Name:       Flatsome - Product Gallery Video
 * Plugin URI:        https://example.com/
 * Description:       Adds a video upload option to the WooCommerce product gallery for the Flatsome theme.
 * Version:           2.0.0
 * Author:            Your Name
 * Author URI:        https://example.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       flatsome-product-gallery-video
 * Domain Path:       /languages
 *
 * WC requires at least: 3.0.0
 * WC tested up to: 8.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * --------------------------------------------------------------------------
 * 1. HOOKS
 * --------------------------------------------------------------------------
 * This section registers all the necessary actions and filters with WordPress.
 */

// Admin Hooks: Add the meta box, save data, and enqueue admin scripts.
add_action( 'add_meta_boxes', 'fpgv_add_meta_box' );
add_action( 'save_post', 'fpgv_save_postdata' );
add_action( 'admin_enqueue_scripts', 'fpgv_enqueue_admin_scripts' );

// Frontend Hooks: Enqueue assets and integrate the video into the gallery.
add_action( 'wp_enqueue_scripts', 'fpgv_enqueue_frontend_assets' );
add_filter( 'woocommerce_product_get_gallery_image_ids', 'fpgv_add_video_thumbnail_to_gallery', 10, 2 );
add_filter( 'woocommerce_single_product_image_thumbnail_html', 'fpgv_add_video_data_to_thumbnail_html', 10, 2 );


/**
 * --------------------------------------------------------------------------
 * 2. META BOX CREATION (Admin Area)
 * --------------------------------------------------------------------------
 * Defines functions for the custom meta box on the product edit screen.
 */

function fpgv_add_meta_box() {
    add_meta_box(
        'fpgv_product_video_metabox',
        __( 'Product Gallery Video', 'flatsome-product-gallery-video' ),
        'fpgv_render_meta_box_content',
        'product',
        'side',
        'low'
    );
}

function fpgv_render_meta_box_content( $post ) {
    wp_nonce_field( 'fpgv_save_meta_box_data', 'fpgv_meta_box_nonce' );

    // Get saved attachment IDs and settings
    $video_id     = get_post_meta( $post->ID, '_product_video_id', true );
    $thumbnail_id = get_post_meta( $post->ID, '_product_video_thumbnail_id', true );
    $auto_resize  = get_post_meta( $post->ID, '_product_video_auto_resize', true );

    // Get file info from IDs for display
    $video_filename = $video_id ? basename( get_attached_file( $video_id ) ) : '';
    $thumbnail_src  = $thumbnail_id ? wp_get_attachment_image_url( $thumbnail_id, 'thumbnail' ) : '';
    ?>
    <style>
        .fpgv-field-wrapper { margin-bottom: 15px; }
        .fpgv-field-wrapper p { margin: 5px 0; }
        .fpgv-remove-button { color: #a00; text-decoration: none; margin-left: 10px; font-weight: bold; }
        #fpgv_video_filename, #fpgv_thumbnail_preview img { margin-top: 10px; display: block; }
        #fpgv_video_filename { font-style: italic; background: #f0f0f1; padding: 4px 8px; border-radius: 3px; }
    </style>

    <!-- Video Uploader -->
    <div class="fpgv-field-wrapper">
        <strong><?php _e( 'Video File', 'flatsome-product-gallery-video' ); ?></strong>
        <input type="hidden" id="fpgv_video_id" name="fpgv_video_id" value="<?php echo esc_attr( $video_id ); ?>" />
        <p>
            <input type="button" id="fpgv_upload_video_button" class="button" value="<?php _e( 'Upload/Choose Video', 'flatsome-product-gallery-video' ); ?>">
        </p>
        <div id="fpgv_video_display" <?php echo $video_id ? '' : 'style="display:none;"'; ?>>
            <span id="fpgv_video_filename"><?php echo esc_html( $video_filename ); ?></span>
            <a href="#" id="fpgv_remove_video_button" class="fpgv-remove-button"><?php _e( 'Remove Video', 'flatsome-product-gallery-video' ); ?></a>
        </div>
    </div>

    <!-- Thumbnail Uploader -->
    <div class="fpgv-field-wrapper">
        <strong><?php _e( 'Video Thumbnail', 'flatsome-product-gallery-video' ); ?></strong>
        <input type="hidden" id="fpgv_thumbnail_id" name="fpgv_thumbnail_id" value="<?php echo esc_attr( $thumbnail_id ); ?>" />
        <p>
            <input type="button" id="fpgv_upload_thumbnail_button" class="button" value="<?php _e( 'Upload/Choose Thumbnail', 'flatsome-product-gallery-video' ); ?>">
        </p>
        <div id="fpgv_thumbnail_display" <?php echo $thumbnail_id ? '' : 'style="display:none;"'; ?>>
            <div id="fpgv_thumbnail_preview">
                <?php if ( $thumbnail_src ) : ?>
                    <img src="<?php echo esc_url( $thumbnail_src ); ?>" style="max-width:100%; height:auto;" />
                <?php endif; ?>
            </div>
            <a href="#" id="fpgv_remove_thumbnail_button" class="fpgv-remove-button"><?php _e( 'Remove Thumbnail', 'flatsome-product-gallery-video' ); ?></a>
        </div>
    </div>

    <hr>
    
    <!-- Auto Resize Checkbox -->
    <p>
        <input type="checkbox" id="fpgv_auto_resize" name="fpgv_auto_resize" value="yes" <?php checked( $auto_resize, 'yes' ); ?> />
        <label for="fpgv_auto_resize"><?php _e( 'Auto Resize Video to Match Gallery', 'flatsome-product-gallery-video' ); ?></label>
    </p>

    <?php
}


/**
 * --------------------------------------------------------------------------
 * 3. SAVING METADATA (Admin Area)
 * --------------------------------------------------------------------------
 * Securely saves data from the custom meta box fields.
 */

function fpgv_save_postdata( $post_id ) {
    if ( ! isset( $_POST['fpgv_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['fpgv_meta_box_nonce'], 'fpgv_save_meta_box_data' ) ) {
        return;
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }
    if ( ! isset( $_POST['post_type'] ) || 'product' !== $_POST['post_type'] ) {
		return;
	}

    // Sanitize and save/delete video ID
    if ( isset( $_POST['fpgv_video_id'] ) && ! empty( $_POST['fpgv_video_id'] ) ) {
        update_post_meta( $post_id, '_product_video_id', absint( $_POST['fpgv_video_id'] ) );
    } else {
        delete_post_meta( $post_id, '_product_video_id' );
    }

    // Sanitize and save/delete thumbnail ID
    if ( isset( $_POST['fpgv_thumbnail_id'] ) && ! empty( $_POST['fpgv_thumbnail_id'] ) ) {
        update_post_meta( $post_id, '_product_video_thumbnail_id', absint( $_POST['fpgv_thumbnail_id'] ) );
    } else {
        delete_post_meta( $post_id, '_product_video_thumbnail_id' );
    }
    
    // Save/delete auto-resize checkbox state
    if ( isset( $_POST['fpgv_auto_resize'] ) ) {
        update_post_meta( $post_id, '_product_video_auto_resize', 'yes' );
    } else {
        delete_post_meta( $post_id, '_product_video_auto_resize' );
    }
}


/**
 * --------------------------------------------------------------------------
 * 4. ADMIN SCRIPTS (Admin Area)
 * --------------------------------------------------------------------------
 * Enqueues JavaScript for the media uploader in the meta box.
 */

function fpgv_enqueue_admin_scripts( $hook ) {
    global $post_type;
    if ( ( 'post.php' === $hook || 'post-new.php' === $hook ) && 'product' === $post_type ) {
        wp_enqueue_media();
        wp_add_inline_script( 'jquery-ui-sortable', fpgv_get_admin_js() );
    }
}

function fpgv_get_admin_js() {
    return "
    jQuery(document).ready(function($){
        var video_frame;
        var thumb_frame;

        // --- Video Upload Logic ---
        $('#fpgv_upload_video_button').on('click', function(e) {
            e.preventDefault();
            if (video_frame) { video_frame.open(); return; }

            video_frame = wp.media({
                title: 'Select or Upload a Video',
                button: { text: 'Use this video' },
                library: { type: 'video' },
                multiple: false
            });

            video_frame.on('select', function() {
                var attachment = video_frame.state().get('selection').first().toJSON();
                $('#fpgv_video_id').val(attachment.id);
                $('#fpgv_video_filename').text(attachment.filename);
                $('#fpgv_video_display').show();
            });
            video_frame.open();
        });

        // --- Remove Video Logic ---
        $('#fpgv_remove_video_button').on('click', function(e) {
            e.preventDefault();
            $('#fpgv_video_id').val('');
            $('#fpgv_video_filename').text('');
            $('#fpgv_video_display').hide();
        });


        // --- Thumbnail Upload Logic ---
        $('#fpgv_upload_thumbnail_button').on('click', function(e) {
            e.preventDefault();
            if (thumb_frame) { thumb_frame.open(); return; }

            thumb_frame = wp.media({
                title: 'Select or Upload a Thumbnail Image',
                button: { text: 'Use this image' },
                library: { type: 'image' },
                multiple: false
            });

            thumb_frame.on('select', function() {
                var attachment = thumb_frame.state().get('selection').first().toJSON();
                $('#fpgv_thumbnail_id').val(attachment.id);
                var thumbnailUrl = attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url;
                $('#fpgv_thumbnail_preview').html('<img src=\"' + thumbnailUrl + '\" style=\"max-width:100%; height:auto;\" />');
                $('#fpgv_thumbnail_display').show();
            });
            thumb_frame.open();
        });

        // --- Remove Thumbnail Logic ---
        $('#fpgv_remove_thumbnail_button').on('click', function(e) {
            e.preventDefault();
            $('#fpgv_thumbnail_id').val('');
            $('#fpgv_thumbnail_preview').html('');
            $('#fpgv_thumbnail_display').hide();
        });
    });
    ";
}


/**
 * --------------------------------------------------------------------------
 * 5. FRONTEND FUNCTIONALITY
 * --------------------------------------------------------------------------
 * Handles the display of the video thumbnail and enqueues assets.
 */

function fpgv_enqueue_frontend_assets() {
    if ( is_product() ) {
        wp_enqueue_style('fpgv-style', plugin_dir_url( __FILE__ ) . 'css/style.css', array(), '2.0.0');
        wp_enqueue_script('fpgv-frontend-js', plugin_dir_url( __FILE__ ) . 'js/frontend.js', array( 'jquery' ), '2.0.0', true);
    }
}

/**
 * Add the video thumbnail ID to the product gallery images.
 * This ensures the thumbnail appears in the gallery slider.
 *
 * @param array      $image_ids Array of gallery image attachment IDs.
 * @param WC_Product $product   The product object.
 * @return array Modified array of image IDs.
 */
function fpgv_add_video_thumbnail_to_gallery( $image_ids, $product ) {
	if ( ! is_a( $product, 'WC_Product' ) ) {
		return $image_ids;
	}

	$thumbnail_id = get_post_meta( $product->get_id(), '_product_video_thumbnail_id', true );

	if ( ! empty( $thumbnail_id ) ) {
		// Add the thumbnail ID to the beginning of the gallery array.
		array_unshift( $image_ids, $thumbnail_id );
	}

	return $image_ids;
}

/**
 * Add video data attributes to the video thumbnail's HTML.
 * This allows the frontend script to identify the video thumbnail and its source.
 *
 * @param string $html          The HTML for the thumbnail image link.
 * @param int    $attachment_id The attachment ID of the thumbnail.
 * @return string Modified HTML.
 */
function fpgv_add_video_data_to_thumbnail_html( $html, $attachment_id ) {
	global $product;
	if ( ! is_a( $product, 'WC_Product' ) ) {
		return $html;
	}

	$product_id         = $product->get_id();
	$video_thumbnail_id = get_post_meta( $product_id, '_product_video_thumbnail_id', true );

	// Check if the current thumbnail is our video thumbnail.
	if ( ! empty( $video_thumbnail_id ) && (int) $attachment_id === (int) $video_thumbnail_id ) {
		$video_id = get_post_meta( $product_id, '_product_video_id', true );
		if ( ! empty( $video_id ) ) {
			$video_url = wp_get_attachment_url( $video_id );
			$auto_resize = get_post_meta( $product_id, '_product_video_auto_resize', true ) === 'yes' ? 'yes' : 'no';

			if ( $video_url ) {
				$data_attributes = sprintf(
					'data-fpgv-video-url="%s" data-fpgv-auto-resize="%s"',
					esc_url( $video_url ),
					esc_attr( $auto_resize )
				);
                // Inject the data attributes into the <a> tag.
				$html = str_replace( '<a ', '<a ' . $data_attributes . ' ', $html );
			}
		}
	}
	return $html;
}
