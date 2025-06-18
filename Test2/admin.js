jQuery(function($){
  function setupMedia(btnSel, targetField, previewSel){
    $(btnSel).on('click', function(e){
      e.preventDefault();
      let frame = wp.media({ multiple: false });
      frame.on('select', () => {
        let att = frame.state().get('selection').first().toJSON();
        $(targetField).val(att.id);
        if(previewSel){
          $(previewSel).attr('src', att.url).show();
        } else {
          $(btnSel).siblings('#fpgv_video_filename').text(att.filename || att.url);
        }
      });
      frame.open();
    });
  }

  setupMedia('#fpgv_upload_video', '#fpgv_video_id');
  setupMedia('#fpgv_upload_thumb', '#fpgv_thumb_id', '#fpgv_thumb_preview');

  $('#fpgv_remove_video').on('click', () => {
    $('#fpgv_video_id').val('');
    $('#fpgv_video_filename').text('');
  });
  $('#fpgv_remove_thumb').on('click', () => {
    $('#fpgv_thumb_id').val('');
    $('#fpgv_thumb_preview').hide().attr('src','');
  });

  let thumbID = $('#fpgv_thumb_id').val();
  if(thumbID>0){
    wp.media.attachment(thumbID).fetch().then(att => {
      $('#fpgv_thumb_preview').attr('src', att.attributes.url).show();
    });
  }
});

jQuery(function($){
  function reinitGallery() {
    if(typeof UXBuilder !== 'undefined' && typeof UXSlider === 'function') {
      $('.product-gallery').each(function(){
        var slider = $(this).data('xuSlider');
        if(slider) slider.destroy();
      });
      UXSlider.init($('.product-gallery'));
      UXLightbox.init($('.product-gallery'));
    }
  }

  $(window).on('load', reinitGallery);
});
