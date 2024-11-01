jQuery.noConflict();
var WelcomePopup = {
    show: function($){
        var pixelRatio = window.devicePixelRatio || 1;
        if ( pixelRatio > 1.5 ) {
          welcome_popup_width  = welcome_popup_width  / pixelRatio;
          welcome_popup_height = welcome_popup_height / pixelRatio;
        }
        if(welcome_popup_embed == 0){
            $.ajax({
                type: 'POST',
                url: welcome_popup_ajaxUrl,
                data: {
                    action: 'wpmt_get_welcome_popup_content',
                    post_id: welcome_popup_content
                },
                dataType: 'html',
                cache: false,
                success: function(response, textStatus, XMLHttpRequest){
                    if(welcome_popup_delays > 0){
                        welcome_popup_delays = welcome_popup_delays - 1;
                    }
                    setTimeout(function(){
                        if (welcome_popup_type == 'fancybox') {
                          $.fancybox.open(`<div style="width:${welcome_popup_width}px;height:${welcome_popup_height}px">${response}</div>`, {
                            transitionDuration: welcome_popup_speed,
                            clickSlide: (welcome_popup_overlayClose) ? "close" : false,
                            clickOutside: (welcome_popup_overlayClose) ? "close" : false
                          });
                        } else {
                          $.colorbox({
                              html: response,
                              width: welcome_popup_width,
                              height: welcome_popup_height,
                              speed: welcome_popup_speed,
                              overlayClose: welcome_popup_overlayClose,
                              fixed: true
                          });
                        }
                    }, welcome_popup_delays * 1000);
                },  
                error: function(MLHttpRequest, textStatus, errorThrown){},
                complete:function(){}
            });
        } else if(welcome_popup_embed == 1){
            setTimeout(function(){
              if (welcome_popup_type == 'fancybox') {
                if (welcome_popup_content.indexOf('watch?v=') !== -1) {
                  welcome_popup_content = welcome_popup_content.replace('watch?v=', 'embed/')
                }
                $.fancybox.open({
                  src  : welcome_popup_content,
                  type : 'iframe',
                  opts : {
                    transitionDuration: welcome_popup_speed,
                    clickSlide: (welcome_popup_overlayClose) ? "close" : false,
                    clickOutside: (welcome_popup_overlayClose) ? "close" : false
                  }
                });
              } else {
                $.colorbox({
                    iframe: true,
                    href: welcome_popup_content,
                    width: welcome_popup_width,
                    height: welcome_popup_height,
                    speed: welcome_popup_speed,
                    overlayClose: welcome_popup_overlayClose,
                    fixed: true
                });
              }
            }, welcome_popup_delays * 1000);
        } else if(welcome_popup_embed == 2){
            $.ajax({
                type: 'POST',
                url: welcome_popup_ajaxUrl,
                data: {
                    action: 'wpmt_get_welcome_popup_custom_content',
                    post_id: welcome_popup_content
                },
                dataType: 'html',
                cache: false,
                success: function(response, textStatus, XMLHttpRequest){
                    if(welcome_popup_delays > 0){
                        welcome_popup_delays = welcome_popup_delays - 1;
                    }
                    setTimeout(function(){
                      if (welcome_popup_type == 'fancybox') {
                        $.fancybox.open(`<div style="width:${welcome_popup_width}px;height:${welcome_popup_height}px">${response}</div>`, {
                          transitionDuration: welcome_popup_speed,
                          clickSlide: (welcome_popup_overlayClose) ? "close" : false,
                          clickOutside: (welcome_popup_overlayClose) ? "close" : false
                        });
                      } else {
                        $.colorbox({
                            html: response,
                            width: welcome_popup_width,
                            height: welcome_popup_height,
                            speed: welcome_popup_speed,
                            overlayClose: welcome_popup_overlayClose,
                            fixed: true
                        });
                      }
                    }, welcome_popup_delays * 1000);
                },  
                error: function(MLHttpRequest, textStatus, errorThrown){},
                complete:function(){}
            });
        }
    }
};
var exit_content = "";
var ExitPopup = {
    setup: function($){
        if(exit_popup_embed == 0){
            $.ajax({
                type: 'POST',
                url: exit_popup_ajaxUrl,
                data: {
                    action: 'wpmt_get_exit_popup_content',
                    post_id: exit_popup_content
                },
                dataType: 'html',
                cache: false,
                success: function(response, textStatus, XMLHttpRequest){
                    exit_content = response;
                },  
                error: function(MLHttpRequest, textStatus, errorThrown){},
                complete:function(){}
            });
        } else if(exit_popup_embed == 2){
            $.ajax({
                type: 'POST',
                url: exit_popup_ajaxUrl,
                data: {
                    action: 'wpmt_get_exit_popup_custom_content',
                    post_id: exit_popup_content
                },
                dataType: 'html',
                cache: false,
                success: function(response, textStatus, XMLHttpRequest){
                    exit_content = response;
                },  
                error: function(MLHttpRequest, textStatus, errorThrown){},
                complete:function(){}
            });
        }
    },
    show: function($){
        var pixelRatio = window.devicePixelRatio || 1;
        if ( pixelRatio > 1.5 ) {
          exit_popup_width  = exit_popup_width  / pixelRatio;
          exit_popup_height = exit_popup_height / pixelRatio;
        }
        if(exit_popup_embed == 1){
          if (exit_popup_type == 'fancybox') {
            if (exit_popup_content.indexOf('watch?v=') !== -1) {
              exit_popup_content = exit_popup_content.replace('watch?v=', 'embed/')
            }
            $.fancybox.open({
              src  : exit_popup_content,
              type : 'iframe',
              opts : {
                transitionDuration: exit_popup_speed,
                clickSlide: (exit_popup_overlayClose) ? "close" : false,
                clickOutside: (exit_popup_overlayClose) ? "close" : false
              }
            });
          } else {
            $.colorbox({
                iframe: true,
                href: exit_popup_content,
                width: exit_popup_width,
                height: exit_popup_height,
                speed: exit_popup_speed,
                overlayClose: exit_popup_overlayClose,
                fixed: true
            });
          }
        } else if(exit_popup_embed == 0 || exit_popup_embed == 2){
            if(exit_content != ""){
              if (exit_popup_type == 'fancybox') {
                $.fancybox.open(`<div style="width:${exit_popup_width}px;height:${exit_popup_height}px">${exit_content}</div>`, {
                  transitionDuration: exit_popup_speed,
                  clickSlide: (exit_popup_overlayClose) ? "close" : false,
                  clickOutside: (exit_popup_overlayClose) ? "close" : false
                });
              } else {
                $.colorbox({
                    html: exit_content,
                    width: exit_popup_width,
                    height: exit_popup_height,
                    speed: exit_popup_speed,
                    overlayClose: exit_popup_overlayClose,
                    fixed: true
                });
              }
            }
        }
    }
};
jQuery(function($){
    $(window).load(function(){
        // Exit
        ExitPopup.setup($);
        $(document).mousemove(function(e) {
          if (e.pageY <= 10) {
            if (exit_popup_type == 'fancybox' && $('.fancybox-container').length === 0) {
              ExitPopup.show($);
            } else if($("#cboxOverlay").is(":hidden")) {
              ExitPopup.show($);
            }
          }
        });

        // Welcome
        WelcomePopup.show($);
        if(!WPMTFunc.getCookie(welcome_popup_cookieName)){
            WPMTFunc.setCookie(welcome_popup_cookieName, welcome_popup_days, 1 * 24*60*60*1000, '/', '', '');
        }else if(WPMTFunc.getCookie(welcome_popup_cookieName) != welcome_popup_days){
            WPMTFunc.setCookie(welcome_popup_cookieName, welcome_popup_days, 1 * 24*60*60*1000, '/', '', '');
        }
    });
});