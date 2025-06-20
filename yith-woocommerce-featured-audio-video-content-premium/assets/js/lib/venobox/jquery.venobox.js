/*
/*
The MIT License (MIT)

Copyright (c) 2013 - 2020 Venobox

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
 *
 */
(function($){

    var ios, ie9, overlayColor, overlay, framewidth, border, bgcolor, frameheight, margine, sonH, finH, numeratio,
        vwrap, container, infinigall, content, core, dest, top, prima, type, thisgall, items, thenext, theprev,
        title, nextok, prevok, keyNavigationDisabled, blocktitle, blocknum, evitanext, evitaprev, evitacontent,
        figliall, extraCss;

    $.fn.extend({
        //plugin name - venobox
        venobox: function(options) {

          // default option
          var defaults = {
              framewidth: '',
              frameheight: '',
              border: '0',
              bgcolor: '#fff',
              titleattr: 'title', // specific attribute to get a title (e.g. [data-title]) - thanx @mendezcode
              numeratio: false,
              infinigall: false,
              overlayclose: true // disable overlay click-close - thanx @martybalandis 
          };

          var option = $.extend(defaults, options);

            return this.each(function() {
                  var obj = $(this);

                  // Prevent double initialization - thanx @matthistuff
                  if(obj.data('venobox')) {
                    return true;
                  }

                  obj.addClass('vbox-item');
                  obj.data('framewidth', option.framewidth);
                  obj.data('frameheight', option.frameheight);
                  obj.data('border', option.border);
                  obj.data('bgcolor', option.bgcolor);
                  obj.data('numeratio', option.numeratio);
                  obj.data('infinigall', option.infinigall);
                  obj.data('overlayclose', option.overlayclose);
                  obj.data('venobox', true);

                  ios = (navigator.userAgent.match(/(iPad|iPhone|iPod)/g) ? true : false);

                  // IE 9 or less
                  ie9 = ((document.all && !window.atob) ? true : false);

                  obj.click(function(e){
                    e.stopPropagation();
                    e.preventDefault();
                    obj = $(this);
                    overlayColor = obj.data('overlay');
                    framewidth = obj.data('framewidth');
                    frameheight = obj.data('frameheight');
                    border = obj.data('border');
                    bgcolor = obj.data('bgcolor');
                    nextok = false;
                    prevok = false;
                    keyNavigationDisabled = false;
                    dest = obj.attr('href');
                    top = $(window).scrollTop();
                    top = -top;
                    extraCss = obj.data( 'css' ) || "";

                    $('body').wrapInner('<div class="vwrap"></div>');

                    vwrap = $('.vwrap');
                    core = '<div class="vbox-overlay ' + extraCss + '" style="background:'+ overlayColor +'"><div class="vbox-preloader">Loading...</div><div class="vbox-container"><div class="vbox-content"></div></div><div class="vbox-title"></div><div class="vbox-num">0/0</div><div class="vbox-close">X</div><div class="vbox-next">next</div><div class="vbox-prev">prev</div></div>';

                    $('body').append(core);

                    overlay = $('.vbox-overlay');
                    container = $('.vbox-container');
                    content = $('.vbox-content');
                    blocknum = $('.vbox-num');
                    blocktitle = $('.vbox-title');

                    content.html('');
                    content.css('opacity', '0');

                    checknav();

                    overlay.css('min-height', $(window).outerHeight());

                    if (ie9) {
                      overlay.animate({opacity:1}, 250, function(){
                        overlay.addClass('open');
                        overlay.css({
                          'min-height': $(window).outerHeight(),
                          height : 'auto'
                        });
                        if(obj.data('type') == 'iframe'){
                          loadIframe();
                        }else if (obj.data('type') == 'inline'){
                          loadInline();
                        }else if (obj.data('type') == 'ajax'){
                          loadAjax();
                        }else if (obj.data('type') == 'vimeo'){
                          loadVimeo();
                        }else if (obj.data('type') == 'youtube'){
                          loadYoutube();
                        } else {
                          content.html('<img src="'+dest+'">');
                          preloadFirst();
                        }
                      });
                    } else {
                      overlay.bind("transitionend webkitTransitionEnd oTransitionEnd MSTransitionEnd", function(e){

                        // Check if transition is on the overlay - thanx @kanduvisla
                        if( e.target != e.currentTarget ) {
                          return;
                        }
                        
                        overlay.css({
                          'min-height': $(window).outerHeight(),
                          height : 'auto'
                        });
                        if(obj.data('type') == 'iframe'){
                          loadIframe();
                        }else if (obj.data('type') == 'inline'){
                          loadInline();
                        }else if (obj.data('type') == 'ajax'){
                          loadAjax();
                        }else if (obj.data('type') == 'vimeo'){
                          loadVimeo();
                        }else if (obj.data('type') == 'youtube'){
                          loadYoutube();
                        } else {
                          content.html('<img src="'+dest+'">');
                          preloadFirst();
                        }
                      });
                      overlay.css('opacity', '1');
                    }

                    if (ios) {
                      vwrap.css({
                        'position': 'fixed',
                        'top': top,
                        'opacity': '0'
                      }).data('top', top);
                    } else {
                      vwrap.css({
                        'position': 'fixed',
                        'top': top
                      }).data('top', top);
                      $(window).scrollTop(0);
                    }

                    /* -------- CHECK NEXT / PREV -------- */
                    function checknav(){

                      thisgall = obj.data('gall');
                      numeratio = obj.data('numeratio');
                      infinigall = obj.data('infinigall');

                      items = $('.vbox-item[data-gall="' + thisgall + '"]');

                      if(items.length > 0 && numeratio === true){
                        blocknum.html(items.index(obj)+1 + ' / ' + items.length);
                        blocknum.fadeIn();
                      }else{
                        blocknum.fadeOut();
                      }

                      thenext = items.eq( items.index(obj) + 1 );
                      theprev = items.eq( items.index(obj) - 1 );

                      if(obj.attr(option.titleattr)){
                        title = obj.attr(option.titleattr);
                        blocktitle.fadeIn();
                      }else{
                        title = '';
                        blocktitle.fadeOut();
                      }

                      if (items.length > 0 && infinigall === true) {

                        nextok = true;
                        prevok = true;

                        if(thenext.length < 1 ){
                          thenext = items.eq(0);
                        }
                        if(items.index(obj) < 1 ){
                          theprev = items.eq( items.index(items.length) );
                        }

                      } else {

                        if(thenext.length > 0 ){
                          $('.vbox-next').css('display', 'block');
                          nextok = true;
                        }else{
                          $('.vbox-next').css('display', 'none');
                          nextok = false;
                        }
                        if(items.index(obj) > 0 ){
                          $('.vbox-prev').css('display', 'block');
                          prevok = true;
                        }else{
                          $('.vbox-prev').css('display', 'none');
                          prevok = false;
                        }
                      }
                    }
                    
                     /* -------- NAVIGATION CODE -------- */
                    var gallnav = {
                      
                      prev: function() {

                        if (keyNavigationDisabled) return; else keyNavigationDisabled = true;

                        overlayColor = theprev.data('overlay');

                        framewidth = theprev.data('framewidth');
                        frameheight = theprev.data('frameheight');
                        border = theprev.data('border');
                        bgcolor = theprev.data('bgcolor');

                        dest = theprev.attr('href');

                        if(theprev.attr(option.titleattr)){
                          title = theprev.attr(option.titleattr);
                        }else{
                          title = '';
                        }

                        if (overlayColor === undefined ) {
                          overlayColor = "";
                        }

                        overlay.css('min-height', $(window).outerHeight());

                        content.animate({ opacity:0}, 500, function(){
                        overlay.css('min-height', $(window).outerHeight()).css('background',overlayColor);

                          if (theprev.data('type') == 'iframe') {
                            loadIframe();
                          } else if (theprev.data('type') == 'inline'){
                            loadInline();
                          } else if (theprev.data('type') == 'ajax'){
                            loadAjax();
                          } else if (theprev.data('type') == 'youtube'){
                            loadYoutube();
                          } else if (theprev.data('type') == 'vimeo'){
                            loadVimeo();
                          }else{
                            content.html('<img src="'+dest+'">');
                            preloadFirst();
                          }
                          obj = theprev;
                          checknav();
                          keyNavigationDisabled = false;
                        });

                      },

                      next: function() {
                        
                        if (keyNavigationDisabled) return; else keyNavigationDisabled = true;

                        overlayColor = thenext.data('overlay');

                        framewidth = thenext.data('framewidth');
                        frameheight = thenext.data('frameheight');
                        border = thenext.data('border');
                        bgcolor = thenext.data('bgcolor');


                        dest = thenext.attr('href');

                        if(thenext.attr(option.titleattr)){
                          title = thenext.attr(option.titleattr);
                        }else{
                          title = '';
                        }

                        if (overlayColor === undefined ) {
                          overlayColor = "";
                        }

                        overlay.css('min-height', $(window).outerHeight());

                        content.animate({ opacity:0}, 500, function(){
                        overlay.css('min-height', $(window).outerHeight()).css('background',overlayColor);

                          if (thenext.data('type') == 'iframe') {
                            loadIframe();
                          } else if (thenext.data('type') == 'inline'){
                            loadInline();
                          } else if (thenext.data('type') == 'ajax'){
                            loadAjax();
                          } else if (thenext.data('type') == 'youtube'){
                            loadYoutube();
                          } else if (thenext.data('type') == 'vimeo'){
                            loadVimeo();
                          }else{
                            content.html('<img src="'+dest+'">');
                            preloadFirst();
                          }
                          obj = thenext;
                          checknav();
                          keyNavigationDisabled = false;
                        });

                      }

                    };

                    /* -------- NAVIGATE WITH ARROW KEYS -------- */
                    $('body').keydown(function(e) {

                      if(e.keyCode == 37 && prevok == true) { // left
                        gallnav.prev();
                      }

                      if(e.keyCode == 39 && nextok == true) { // right
                        gallnav.next();
                      }

                    });

                    /* -------- PREVGALL -------- */
                    $('.vbox-prev').click(function(){
                      gallnav.prev();
                    });
                    
                    /* -------- NEXTGALL -------- */
                    $('.vbox-next').click(function(){
                      gallnav.next();
                    });
                    
                    /* -------- ESCAPE HANDLER -------- */
                    function escapeHandler(e) {
                      if(e.keyCode === 27) {
                        closeVbox();
                      }
                    }

                    /* -------- CLOSE VBOX -------- */

                    function closeVbox(){
                      
                      $('body').unbind('keydown', escapeHandler);



                        if (ie9) {

                        overlay.animate({opacity:0}, 500, function(){
                          overlay.remove();
                          $('.vwrap').children().unwrap();
                          $(window).scrollTop(-top);
                          keyNavigationDisabled = false;
                          obj.focus();
                        });

                      } else {

                        overlay.unbind("transitionend webkitTransitionEnd oTransitionEnd MSTransitionEnd");
                        overlay.bind("transitionend webkitTransitionEnd oTransitionEnd MSTransitionEnd", function(e){

                          // Check if transition is on the overlay - thanx @kanduvisla
                          if( e.target != e.currentTarget ) {
                            return;
                          }

                          overlay.remove();
                          if (ios) {
                            $('.vwrap').bind("transitionend webkitTransitionEnd oTransitionEnd MSTransitionEnd", function(){
                              $('.vwrap').children().unwrap();
                              $(window).scrollTop(-top);
                            });
                            $('.vwrap').css('opacity', '1');
                          }else{
                            $('.vwrap').children().unwrap();
                            $(window).scrollTop(-top);
                          }
                          keyNavigationDisabled = false;
                          obj.focus();
                        });
                          overlay.css('opacity', '0');
                          overlay.removeClass('open');
                          overlay.addClass('close');
                      }
                      //added by yithemes

                        var video_content = $('.vbox-inline .ywcfav-video-content.host'),
                            video_id = video_content.find('video').attr('id');

                        if( typeof video_id !== 'undefined' ) {
                            videojs(video_id).dispose();
                        }

                      $('body').trigger('venobox-closed', [overlay]);
                    }

                    /* -------- CLOSE CLICK -------- */
                    var closeclickclass = '.vbox-close, .vbox-overlay';
                    if(!obj.data('overlayclose')){
                        closeclickclass = '.vbox-close';    // close only on X
                    }

                    $(closeclickclass).click(function(e){
                      evitacontent = '.figlio';
                      evitaprev = '.vbox-prev';
                      evitanext = '.vbox-next';
                      figliall = '.figlio *';
                      if(!$(e.target).is(evitacontent) && !$(e.target).is(evitanext) && !$(e.target).is(evitaprev)&& !$(e.target).is(figliall)){
                        closeVbox();
                      }
                    });
                    $('body').keydown(escapeHandler);

                    return false;
                  });
            });
        }
    });

    /* -------- LOAD AJAX -------- */
    function loadAjax(){
      $.ajax({
      url: dest,
      cache: false
      }).done(function( msg ) {
          content.html('<div class="vbox-inline">'+ msg +'</div>');
          updateoverlay(true);

      }).fail(function() {
          content.html('<div class="vbox-inline"><p>Error retrieving contents, please retry</div>');
          updateoverlay(true);
      })
    }

    /* -------- LOAD IFRAME -------- */
    function loadIframe(){
      content.html('<iframe class="venoframe" src="'+dest+'"></iframe>');
    //  $('.venoframe').load(function(){ // valid only for iFrames in same domain
      updateoverlay();
    //  });
    }

    /* -------- LOAD VIMEO -------- */
    function loadVimeo(){
      var pezzi = dest.split('/');
      var videoid = pezzi[pezzi.length-1];
      content.html('<iframe class="venoframe" src="//player.vimeo.com/video/'+videoid+'"></iframe>');
      updateoverlay();
    }

    /* -------- LOAD YOUTUBE -------- */
    function loadYoutube(){
      var pezzi = dest.split('/');
      var videoid = pezzi[pezzi.length-1];
      content.html('<iframe class="venoframe" allowfullscreen src="//www.youtube.com/embed/'+videoid+'"></iframe>');
      updateoverlay();
    }

    /* -------- LOAD INLINE -------- */
    function loadInline(){
      content.html('<div class="vbox-inline">'+$(dest).html()+'</div>');
      updateoverlay();
    }

    /* -------- PRELOAD IMAGE -------- */
    function preloadFirst(){
        prima = $('.vbox-content').find('img');
        prima.one('load', function() {
          updateoverlay();
        }).each(function() {
          if(this.complete) $(this).load();
        });
    }

    /* -------- CENTER ON LOAD -------- */
    function updateoverlay(notopzero){
      notopzero = notopzero || false;
      if (notopzero != true) {
        $(window).scrollTop(0);
      }

      blocktitle.html(title);
      content.find(">:first-child").addClass('figlio');
      $('.figlio').css('width', framewidth).css('height', frameheight).css('padding', border).css('background', bgcolor);

      sonH = content.outerHeight();
      finH = $(window).height();

      if(sonH+80 < finH){
        margine = (finH - sonH)/2;
        content.css('margin-top', margine);
        content.css('margin-bottom', margine);

      }else{
        content.css('margin-top', '40px');
        content.css('margin-bottom', '40px');
      }
      content.animate({
        'opacity': '1'
      },'slow', 'swing');
        overlay.addClass('open');
    }

    /* -------- CENTER ON RESIZE -------- */
    function updateoverlayresize(){
      if($('.vbox-content').length){
        sonH = content.height();
        finH = $(window).height();

        if(sonH+80 < finH){
          margine = (finH - sonH)/2;
          content.css('margin-top', margine);
          content.css('margin-bottom', margine);
        }else{
          content.css('margin-top', '40px');
          content.css('margin-bottom', '40px');
        }
      }
    }

    $(window).resize(function(){
      updateoverlayresize();
    });

})(jQuery);