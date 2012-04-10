///* MonkeyPatch jquery fix IE/ */
//
//( function( global ) {
//  var jQuery,
//      origRemoveAttr;
//
//  if( global.jQuery )
//  {
//    jQuery = global.jQuery;
//    origRemoveAttr = jQuery.removeAttr;
//
//    jQuery.removeAttr = function( elem, name )
//    {
//      if( ! jQuery.support.getSetAttribute && ( '' + name ).toUpperCase() === 'ID' )
//      {
//        elem.removeAttribute( name );
//      }
//      else
//      {
//        origRemoveAttr.apply( jQuery, arguments );
//      }
//    };
//  }
//}( window ) );


( function($) {

  Psc_getImageResource = function(name) {
    return '/img/cms/'+name;
  }
  
  Psc_addSpinner = function($container) {
    return;
    
    if ($container.find('span.psc-cms-ui-spinner').length == 0) 
      $container.append('<span class="psc-cms-ui-spinner"><img src="'+Psc.getImageResource('ajax-loader.gif')+'" alt="loading..." /></span>');
  }
  
  Psc_removeSpinner = function($container, callb) {
    return callb($container);
    
    var $spinner = $container.find('span.psc-cms-ui-spinner');
    
    if ($spinner.length > 0) {
      $spinner.fadeOut(function () {
        if (callb) {
          callb($container);
          
        }
        $(this).remove();
      });
      
    }
    
    return null;
  }
  
  Psc_extractIdRx = function(value, pattern) {
    try {
      var m = value.match(pattern);
      if (m && m[1]) {
        return parseInt(m[1]);
      }
    } catch (e) {
    }
    
    return null;
  }
  
  Psc_qmatch = function (value, pattern, index) {
    index = index || 1;
    try {
      var m = value.match(pattern);
      if (m && m[index]) {
        return m[index];
      }
    } catch (e) {}
    
    return null;
  }
  
  Psc_getGUID = function ($element) {
    return Psc_qmatch($element.attr('class'), /psc\-guid\-(.*?)(\s+|$)/,1);
  };
  
  /* UI */
  Psc_disappear = function($element) {
    $element.fadeOut(400);
  }

}) (jQuery);