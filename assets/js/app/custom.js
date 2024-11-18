function baseUrl(url){
    return window.location.origin + '/booking/' + url;
}

;(function($){
    $.fn.extend({
      donetyping: function(callback,timeout){
        timeout = timeout || 1e3;
        var timeoutReference,
            doneTyping = function(el){
              if (!timeoutReference) return;
              timeoutReference = null;
              callback.call(el);
            };
        return this.each(function(i,el){
          var $el = $(el);
          $el.is(':input') && $el.on('keyup keypress paste',function(e){
            if (e.type=='keyup' && !([8,46].includes(e.keyCode))){return;}
            if (timeoutReference) clearTimeout(timeoutReference);
            timeoutReference = setTimeout(function(){
              doneTyping(el);
            }, timeout);
          }).on('blur',function(){
            doneTyping(el);
          });
        });
      }
    });
  })(jQuery);