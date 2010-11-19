$(document).ready (function(){
    var errorId = 'errors';
    var errorShadowId = 'errors-shadow';
    var errorJsonId = 'errors-json';
    
    if ($('#'+errorJsonId).length == 1) {
      $('#'+errorJsonId).hide();
      errstack = $.json.decode( $('#'+errorJsonId).html() );
      if (errstack.length) {
        $('body').prepend("<div id=\""+errorId+"\"></div>");

        var messages = '<ul>';
        var types = {'error':3, 'warning':2, 'ok':1};
        var level = 1;
        for (i=0; i<errstack.length; i++) {
          messages += "<li>"+errstack[i].msg+"</li>";

          level = Math.max(level,types[errstack[i].type]);
        }
        messages += '</ul>';

        if (level >= 3) {
          bgcol = "#f6d4d8"; 
          fontcol = '#DC5757';
        }
        if (level == 2) {
          bgcol = "#F7FDCB";
          fontcol = '#888888';
        } 
        if (level <= 1) {
          bgcol = "#A6EF7B";
          fontcol = '#555555';
        }
      
        var $msg = $('<div style="position: fixed; right: 0px; top: 0px; height: 1px; width:100%;background-color: '+bgcol+'; vertical-align: top; border-top: 1px solid #999999; z-index:99; text-align: left; overflow:hidden;">'+messages+'</div>');
        $('ul',$msg).css({marginTop:'15px'});
        $('ul li',$msg).css({'font-weight':'bold','color':fontcol,'list-style-type':'none','list-style-position':'inside'});
        $('body').prepend ($msg);

        var height = $('ul',$msg).height()+30;

        $('body').animate({marginTop:height}, 200, 'swing');

        $msg.animate({height:height}, 200, 'swing', function() {
            $(this).bind('click',function () {
                $('body').animate({marginTop: 0}, 200, 'swing');
                $(this).animate({height: 1}, 200, 'swing', function() {
                    $(this).remove();
                  });
              });
          });
      }
    }
  });