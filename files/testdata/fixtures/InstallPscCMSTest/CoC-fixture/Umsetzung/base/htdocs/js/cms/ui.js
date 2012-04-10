(function( $ ){
  var $spinner = $('<span class="psc-ui-spinner"><img src="/img/cms/ajax-spinner-small.gif" alt="loading..." /></span>'), spinnerStack = [], spinnerContainer = null;
  
    var methods = {
      'tabs': {
        'close': function($deprecated, $tab) {
          var $tabs = $('#tabs');
          $tab = $tab || $tabs.find('ul.ui-tabs-nav li.ui-tabs-selected');
          
          if ((!$.pscUI('tabs','isUnsaved', $tab)) || confirm('Der Tab hat noch nicht gespeicherte Änderungen, Wenn der Tab geschlossen wird, ohne ihn zu Speichern, gehen die Änderungen verloren.') == true) {
            var index = $("li", $tabs).index( $tab );

            $tabs.tabs( "remove", index );
          }
        },
        'openContent': function($deprecated, item) {
          /* wir wollen irgendwelchen content für die tabs öffnen,
            dies geben wir direkt an eine ajax funktion weiter, die uns den namen des Tabs und dessen ajax-url (remote url) mitteilt */
          var $tabs = $('#tabs');
          
          /* tci hat url und label gespeichert */
          if (false) {
            return $.pscUI('tabs','ui.openContent', $tabs, tci.getIdentifier(), tci.getURL(), tci.getLabel());
          }
          
          /* shortcoming ohne ajax request */
          if (item.id && item.url && item.label) {
            return $.pscUI('tabs','ui.openContent', $tabs, item.id, item.url, item.label);
          }
          
          return $.ajax({
            url : '/ajax.php?todo=tabs&ctrlTodo=content.info',
            type: 'POST',
            global: false,
            data: {
              'type': item.type,
              'identifier': item.identifier,
              'data': item.data || {}
            },
            dataType: 'json',
            success: function(data) {
              if (data.status == 'ok') {
                $.pscUI('tabs','ui.openContent', $tabs, data.content.id, data.content.url, data.content.label);
              }
              
              if (data.status == 'failure') {
                alert(data.content);
              }
            }
          });
        },
        'ui.openContent': function($deprecated, tabId, link, label, $target) {
          var $tabs = $('#tabs');
          $tab = $tabs.find('li a[href="#'+tabId+'"]');
          
          if ($tab.length == 1) {
            /* schon vorhanden, also nur selecten */
            $tabs.tabs('select', tabId);
          } else {
            var openCB = function () {
              /* tab hinzufügen (als inpage tab) */
              $tabs.tabs('add', tabId, label);
              
              $tab = $tabs.find('li:last'); // gerade hinzugefügt
          
              var index = $tabs.tabs('length')-1;
  
              /* dann ajax raus machen */
              $tabs.tabs('url', index, link); // make remote tab
            };
            
            if ($target) {
              $target.effect('transfer', { to: $tabs.find('ul.ui-tabs-nav') }, 500, openCB);
            } else {
              openCB();
            }
          }
        },
        'ui.closeContent': function ($item) {
          var $tabs = $('#tabs');
          var $tab = $tabs.find('li a[href="#'+$item.tabsContentItem('tabsId')+'"]');
          var index = $tabs.find("li").index( $tab );
          $tabs.tabs( "remove", index );
        },
        'isUnsaved': function($tab) {
           return $tab.find('a span.unsaved').length >= 1;
        },
        'unsaved': function($tabs, $tab) {
          var $a = $tab.find('a');
          
          if ($a.find('span.unsaved').length == 0) {
            $a.append('<span class="unsaved">&nbsp;*</span>');
          }
          
          $tabs.find($a.attr('href')).find('button.save').css('font-weight','bold');
        },
        'saved': function($tabs, $tab) {
          var $a = $tab.find('a'),
              $span = $a.find('span.unsaved')
          ;
          if ($span.length >= 1) {
            $span.remove();
          }
    
          $tabs.find($a.attr('href')).find('button.save').css('font-weight','normal');
        },
        
        'reload': function($deprecated, $tab) {
          var $tabs = $('#tabs');
          if (!$.pscUI('tabs','isUnsaved', $tab) || confirm("Der Tab hat noch nicht gespeicherte Änderungen, Wenn der Tab neugeladen wird gehen die Änderungen verloren.") == true) {
            var index = $("li", $tabs).index( $tab );
            $tabs.tabs('load',index);
            $tab.trigger('saved');
          }
        }
      },
      'effects': {
        'blink': function ($element, color, callback) {
          if (!color) color = '#880000';
          var oldColor = $element.css('background-color');
          
          var time = 80;
          $element.animate({
            backgroundColor: color
		  }, time, function () {
            $element.animate({
              backgroundColor: oldColor
            }, time*2, callback);
          });
        }
      },
      'ui': {
        'showSpinner' : function (xhr) {
          spinnerStack.push(xhr);
          
          xhr.complete(function (xhr) {
            $.pscUI('ui','removeSpinner', xhr);
          });
          
          if (spinnerContainer == null) {
            spinnerContainer = $('body .spinner-container');
            
            if (spinnerContainer.length) {
              $spinner.hide();
              spinnerContainer.append($spinner);
            } else {
              spinnerContainer = false; // prevents from rechecking
            }
          }
          
          $spinner.fadeIn(100);
        },
        'removeSpinner' : function (xhr) {
          spinnerStack.pop();
          
          if (spinnerStack.length == 0) {
            $spinner.fadeOut(100);
          }
        }
      },
      'form': {
         /* die box muss die div.drop-box sein
           item.type
           item.identifier
           item.data
        */
        'addItemToBox': function ($box, item) {
          $.pscUI('form','ui.addItemToBox', $box, item);
          $box.trigger('unsaved');
        },
        'ui.addItemToBox': function ($box, item) {
          // cool wäre hier, zu schauen, ob es das item schon als html element gibt
          // und dieses dann zu clonen
          
          var $item = $.data(item, 'contentTabsItem') || [];
          if ($item.length) {
            item = $item.getExport();
          }
          
          // ansonsten machen wir den ajax request und erstellen uns das item per PHP
          $.ajax({
            url : '/ajax.php?todo=tabs&ctrlTodo=content.button',
            type: 'POST',
            global: false,
            data: {
              'type': item.type,
              'identifier': item.identifier,
              'data': item.data || {}
            },
            dataType: 'json',
            success: function(data) {
              if (data.status == 'ok') {
                var button = $(data.content.html)[0];
                    $button = $(button);
                    $button.tabsContentItem(data.content.options)
                           .button({label: data.content.label})
                  ;
                $box.dropBox('appendButton',$button);
              }
              
              if (data.status == 'failure') {
                alert(data.content);
              }
            }
          });
        },
        /**
         * wenn callback false zurückgibt wird der neue tab nicht geöffnet
         */
        'save': function ($form, callback) {
          try {
           /* wir holen uns alle items aus dem formular mit den nötigen informationen und bauen daraus ein paket welches wir per ajax posten */
            $form = $form.is('form') ? $form : $form.find('form');
            
            var additionalData = $.pscUI('form','serialize', $form);
            
            $form.ajaxSubmit({
              // dann steht da undefined drin bei delete, nicht so schön
              dataType: 'json',
              type: 'POST',
              url : $form.attr('action') || '/ajax.php?todo=ctrl&ctrlTodo=save',
              data : additionalData,
              success: function(data) {
                if (data.status == 'ok') {
                  $form.trigger('saved');
                  
                  var $tabs = $('#tabs');

                  if (data.tab && data.tab.close == true) {
                    return $form.trigger('close');
                  }
                    
                  var open = true;
                  if (callback) {
                    open = callback();
                  }
                  
                  if (open != false && data.tab) {
                    /* neuen Tab öffnen */
                    $.pscUI('tabs','openContent', null, data.tab);
                  }
                  
                  if ($form.hasClass('psc-cms-ui-refresh-right')) {
                    $.post('/ajax.php?todo=ctrl&ctrl=cms&ctrlTodo=ui.right.content', {
                            type: 'cms',
                            identifier: null,
                            data: {}
                          },
                          function (data) {
                            $('#drop-contents').html(data);
                          }
                         );
                  }
                }
                    
                if (data.status == 'failure') {
                  // @TODO Validation
                  alert(data.content);
                }
             }
            });
          
          } catch (e) {
            alert(e+"\nDie Daten wurden NICHT gespeichert.");
            throw e;
          }
        },
        'serialize': function ($form) {
          var data = {};
          
          /* buttons in dropboxen holen */
          $form.find('div.psc-cms-ui-drop-box').each(function () {
            $(this).dropBox('serialize', data);
          });
          
          /* Comboboxen im Select Modus holen */
          $form.find('input.psc-cms-ui-combo-box').each(function () {
            $(this).comboBox('serialize', data);
          });
          
          return data;
        },
        
        /* Achtung, dies muss ein tabsContentItem sein! */
        'deleteItem': function ($item) {
          if (!confirm('Sie sind Dabei den Eintrag unwiderruflich zu löschen! Fortfahren?')) {
            return;
          }
          
          return $.ajax({
            url : '/ajax.php?todo=tabs&ctrlTodo=content.delete',
            type: 'POST',
            global: false,
            data: $item.tabsContentItem('getExport'),
            dataType: 'json',
            success: function(data) {
              if (data.status == 'ok') {
                $item.trigger('delete', [$item]);
                $item.trigger('close');
              }
              
              if (data.status == 'failure') {
                alert(data.content);
              }
            }
          });
        }
      }
    };
    
    $.pscUI = function( ns, method ) {    
      if (methods[ns]) {
        if (methods[ns][method]) {
          return methods[ns][method].apply( this, Array.prototype.slice.call( arguments, 2 ));
  //    } else if ( typeof method === 'object' || ! method ) {
  //      return methods.init.apply( this, arguments );
        } else {
          return $.error( 'Method ' +  method + ' existiert nicht in jQuery.pscUI. '+ns );
        }
      } else {
        return $.error( 'Namespace ' +  ns + ' existiert nicht in  jQuery.pscUI ' );
      }
    };
    
})(jQuery);

$(window).load(function() {
  /* jquery ajax setup mit spinner */
  $.ajaxSetup({
    beforeSend: function( xhr ) {
      
      if (xhr.complete) {
        /* show spinner */
        $.pscUI('ui','showSpinner', xhr);
      }
    }
  });
  
  var $tabs = $('#content #tabs');

  /* Tabs erstellen */
  $tabs.tabs({
    tabTemplate: '<li><a href="#{href}" title="#{href}">#{label}<span class="load"></span></a><span class="ui-icon ui-icon-close">Remove Tab</span></li>',
    spinner: false,
    cache: true, // nicht jedes mal remote tabs neu laden, das wollen wir nicht wegen save!
    // das ist kopiert aus ui.tabs und will break bei updates
    ajaxOptions: {
      dataType: 'html',
      error: function( xhr, status, index, anchor ) {
        $( anchor.hash ).html( xhr.responseText);
      }
    }
  });
  
  $tabs.find('ul.ui-tabs-nav').droppable({
    hoverClass: 'hover',
    drop: function (event, ui) {
      var item = ui.draggable.tabsContentItem().tabsContentItem('openTab');
    }
  });
  
  /* sortieren ist noch nicht so richtig geil */
  //$tabs.find('ul.ui-tabs-nav').sortable({ axis: "x",
  //                                        forceHelperSize: true,
  //                                        helper: function(event, element) {
  //                                          var w = element.outerWidth()
  //                                          element.css('width',w+'px');
  //                                          return element;
  //                                        }
  //                                      });
  
  /* Tabs wieder schließen */
  $( "#tabs span.ui-icon-close" ).live( "click", function(e) {
    $.pscUI('tabs','close', null, $(this).parent());
  });
  
  /* von der rechten Seite aus Inhalte öffnen */
  $('#drop-contents').bind('click', function (e) {
    var $target = $(e.target);
    
    if ($target.hasClass('psc-cms-ui-drop-content-ajax-call')) {
      e.preventDefault();
      var id = Psc_getGUID($target);
      $.pscUI('tabs','ui.openContent',$tabs, id, $target.attr('href'), $target.text(), $target);
    }
  });
  
  /* Tabs als bearbeitet markieren / save button bold
     es wird immer der active Tab genommen
  */
  $tabs.bind('unsaved',function (e) {
    var $tab = $tabs.find('ul.ui-tabs-nav li.ui-tabs-selected');
    
    $.pscUI('tabs','unsaved', $tabs, $tab);
  });
  
  $tabs.bind('saved',function (e) {
    var $tab = $tabs.find('ul.ui-tabs-nav li.ui-tabs-selected');
     
    $.pscUI('tabs','saved', $tabs, $tab);
  });
  
  $tabs.bind('reload',function (e) {
    var $tab = $tabs.find('ul.ui-tabs-nav li.ui-tabs-selected');
    
    $.pscUI('tabs','reload', $tabs, $tab);
  });

  $tabs.bind('close',function (e) {
    $.pscUI('tabs','close');
  });
  
    /* Content Item Form  - sendet alles mit ajax-Form ab */  
  $('div.content-item-form').live('click',function(e) {
    if (e.target.nodeName != 'BUTTON' && e.target.nodeName != 'SPAN') return; 
    if (e.isDefaultPrevented()) return;

    var $target = $(e.target);

    if (e.target.nodeName == 'SPAN' && $target.hasClass('ui-button-text')) { // fix für chrome
      $target = $target.parent('button');
    }

    /* das muss leider hier rein, da wir vorher keine form haben (ajax) */
    var $form = $(this);
      
    if ($target.hasClass('psc-cms-ui-button-save')) {
      e.preventDefault();
      
      /* Speichern */
      $.pscUI('form','save',$form);
      
      return;
    }

    if ($target.hasClass('psc-cms-ui-button-save-close')) {
      e.preventDefault();
      
      /* Speichern und Schließen */
      $.pscUI('form','save', $form, function () {
        $form.trigger('close');
        return false; // prevent opening new tab
      });
      
      return;
    }
    
    if ($target.hasClass('psc-cms-ui-button-reload')) {
      e.preventDefault();
      $target.trigger('reload');
      
      return;
    }
    
    /* Wenn ein TCI bis hier hochbubbelt, haben wir es bereits bearbeitet oder es mach nichts
      dann wollen wir vermeidne, dass es die Seite neulädt */
    if ($target.hasClass('tabs-content-item')) {
      e.preventDefault();
      return;
    }
  });
  
    /* Normale Form (eine ohne content tabs item) */
  $('div.psc-cms-ui-form').live('click', function (e) {
    if (e.target.nodeName != 'BUTTON' && e.target.nodeName != 'SPAN') return; 
    if (e.isDefaultPrevented()) return;

    var $target = $(e.target);

    if (e.target.nodeName == 'SPAN' && $target.hasClass('ui-button-text')) { // fix für chrome
      $target = $target.parent('button');
    }
    
    var $form = $(this);
    
    if ($target.hasClass('psc-cms-ui-button-save')) {
      e.preventDefault();
      
      /* Speichern */
      $.pscUI('form','save',$form);
      
      return;
    }

    if ($target.hasClass('psc-cms-ui-button-save-close')) {
      e.preventDefault();
      
      /* Speichern und Schließen */
      $.pscUI('form','save', $form, function () {
        $form.trigger('close');
        return false; // prevent opening new tab
      });
      
      return;
    }
    
    if ($target.hasClass('psc-cms-ui-button-reload')) {
      e.preventDefault();
      $target.trigger('reload');
      
      return;
    }
  });
  
  /* Globales Delete Event entfernt auch items aus der drop-contents-list */
  $('body').bind('delete',function (e, $item) {
    var tci = $item.data('tabsContentItem');
    
    if (tci && tci.getExport()) {
      var item = tci.getExport();
      
      $('#drop-contents').find('ul.psc-cms-ui-drop-contents-list li:has(a.psc-guid-'+item.type+'-'+item.identifier+')')
        .each(function () {
          Psc_disappear($(this));
      });
    }
  });
  
  /* Formular Gruppen klappbar machen */
  $('body').bind('click', function(e) {
    if (e.target.nodeName == 'LEGEND') {
      var $target = $(e.target), $fieldset = null;
    
      if($target.hasClass('collapsible') && ($fieldset = $target.parent('fieldset')) && $fieldset.is('.psc-cms-ui-group')) {
        $fieldset.find('div.content:first').toggle();
      }
    }
    
    if (e.target.nodeName == 'A') {
      var $target = $(e.target);
      if ($target.hasClass('psc-cms-ui-tabs-item')) {
        e.preventDefault();
        var id = Psc_getGUID($target);
        if (id != null) {
          $.pscUI('tabs','ui.openContent', null, id, $target.attr('href'), $target.text(), $target);
        } else {
          throw 'guid ist nicht gesetzt von '+$target.html();
        }
      }
    }
  });
  
  $('#tabs div.psc-cms-ui-form').live('change keyup',function(e) {
    if ($(this).hasClass('unbind-unsaved')) return;
    
    var $target = $(e.target);
    if ($target.attr('readonly') == 'readonly')
      return;
    
    if (e.target.nodeName == 'INPUT' || e.target.nodeName == 'TEXTAREA' || e.target.nodeName == 'SELECT') {
      $target.trigger('unsaved');
      return;
    }
  });
});