// make it safe to use console.log always (paul irish)
(function(b){function c(){}for(var d="assert,count,debug,dir,dirxml,error,exception,group,groupCollapsed,groupEnd,info,log,markTimeline,profile,profileEnd,time,timeEnd,trace,warn".split(","),a;a=d.pop();)b[a]=b[a]||c})(window.console=window.console||{});


(function($) {
  
  $.psc = {
    deferred: $.Deferred(),
    
    loaded: function() {
      return this.deferred.promise();
    },
    
    resolve: function(main) {
      this.deferred.resolve(main);
    },

    reject: function(error) {
      this.deferred.reject(error);
    }
  };

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

})(jQuery);