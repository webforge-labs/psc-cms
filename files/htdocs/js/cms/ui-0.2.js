(function($){
  use(['Psc.UI.Main','Psc.AjaxFormHandler','Psc.Exception'], function () {
    var methods = {
        'tabs': {
          'openContent': function($deprecated, item) {
            /* wir wollen irgendwelchen content für die tabs öffnen,
              dies geben wir direkt an eine ajax funktion weiter, die uns den namen des Tabs und dessen ajax-url (remote url) mitteilt */
            var $tabs = $('#tabs');
            
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
  });
})(jQuery);

$(window).load(function() {
  use(['Psc.UI.Main','Psc.UI.Tabs'], function () {
    var cmsContent = $('#content');
    if (cmsContent.length) {
      // bootstrap Main
      var $tabs = $('#content div.psc-cms-ui-tabs:eq(0)'); // das erste tabs objekt wird unser main tab (hihi)
      var main = new Psc.UI.Main({
        tabs: new Psc.UI.Tabs({
          widget: $tabs
        })
      });
      
      main.attachHandlers();
    
      /*
        resolve für alle Elemente die sich während des Ladens (z. B. per Inline) an das deferred gebindet haben
     
        $.when( $.psc.loaded() ).then(function (main) {
          console.log(main, 'is loaded');
        });
      */
      $.psc.resolve(main);
      
      /* sortieren ist noch nicht so richtig geil */
      //$tabs.find('ul.ui-tabs-nav').sortable({ axis: "x",
      //                                        forceHelperSize: true,
      //                                        helper: function(event, element) {
      //                                          var w = element.outerWidth()
      //                                          element.css('width',w+'px');
      //                                          return element;
      //                                        }
      //                                      });
      
      /* von der rechten Seite aus Inhalte öffnen */
      $('#drop-contents').on('click', 'a.psc-cms-ui-drop-content-ajax-call', function(e) {
        var $target = $(e.target);
        var eventManager = main.getEventManager();
  
        e.preventDefault();
        e.stopPropagation();
        
        eventManager.trigger(
          eventManager.createEvent('tab-open', {source: 'dropContents'}), [
            new Psc.UI.Tab({
              id: Psc_getGUID($target),
              url: $target.attr('href'),
              label: $target.text()
            }),
            $target
          ]
        );
      });
      
      /* Globales Delete Event entfernt auch items aus der drop-contents-list */
      $('body').on('delete', function (e, $item) {
        var tci = $item.data('tabsContentItem');
        
        if (tci && tci.getExport()) {
          var item = tci.getExport();
          
          $('#drop-contents').find('ul.psc-cms-ui-drop-contents-list li:has(a.psc-guid-'+item.type+'-'+item.identifier+')')
            .each(function () {
              Psc_disappear($(this));
          });
        }
      });
      
      setTimeout(function () {
        var $layoutManager = $('#layout-manager');
        var $accordion = $layoutManager.find('div.psc-cms-ui-accordion');
        var $layout = $layoutManager.find('div.psc-cms-ui-splitpane fieldset.psc-cms-ui-group');
        var $layoutContent = $layout.find('div.content');
        var createWidget = function (title) {
          var innerWidget;
          if (title == 'Paragraph') {
            innerWidget = '<textarea class="paragraph" name="" cols="120" rows="5" style="width: 100%; min-height="120px"></textarea>';
          } else if (title === 'Headline' || title === 'Sub-Headline') {
            innerWidget = '<input type="text" style="width: 100%" />';
          } else if (title === 'List') {
            innerWidget = '<input type="text" style="width: 100%" /><br /><input type="text" style="width: 100%" /><br /><input type="text" style="width: 100%" /><br />';
          } else {
            innerWidget = '';
          }
          var $widget = $('<div class="widget"><h3 class="widget-header"></h3><div class="widget-content">'+innerWidget+'</div></div>');
            $widget
              .addClass("ui-widget ui-widget-content ui-helper-clearfix ui-corner-all")
              .on('click', '.widget-header span.ui-icon-close', function(e) {
                e.preventDefault();
                Psc_disappear($widget);
              })
              .css('margin-bottom','5px')
            
            $widget.find('.widget-header')
              .html(title)
              .addClass("ui-helper-reset ui-state-default ui-corner-all" )
              .prepend("<span class='ui-icon ui-icon-close'></span>")
              .css({
                padding: '0.5em 0.5em 0.5em 0.7em'
              })
              .find('.ui-icon')
                .css({
                  'float':'right',
                  cursor: 'pointer'  
                });
            
            $widget.find('.widget-content')
              .css('padding', '1.1em')
              
          return $widget;
        };
        
  $.ui.plugin.add("draggable", "connectMorphSortable", {
      start: function(event, ui) {
  
          var inst = $(this).data("draggable"), o = inst.options,
              uiSortable = $.extend({}, ui, { item: inst.element });
          inst.sortables = [];
          $(o.connectMorphSortable).each(function() {
              var sortable = $.data(this, 'sortable');
              if (sortable && !sortable.options.disabled) {
                  inst.sortables.push({
                      instance: sortable,
                      shouldRevert: sortable.options.revert
                  });
                  sortable.refreshPositions();	// Call the sortable's refreshPositions at drag start to refresh the containerCache since the sortable container cache is used in drag and needs to be up to date (this will ensure it's initialised as well as being kept in step with any changes that might have happened on the page).
                  sortable._trigger("activate", event, uiSortable);
              }
          });
  
      },
      stop: function(event, ui) {
  
          //If we are still over the sortable, we fake the stop event of the sortable, but also remove helper
          var inst = $(this).data("draggable"),
              uiSortable = $.extend({}, ui, { item: inst.element });
  
          $.each(inst.sortables, function() {
              if(this.instance.isOver) {
  
                  this.instance.isOver = 0;
  
                  inst.cancelHelperRemoval = true; //Don't remove the helper in the draggable instance
                  this.instance.cancelHelperRemoval = false; //Remove it in the sortable instance (so sortable plugins like revert still work)
  
                  //The sortable revert is supported, and we have to set a temporary dropped variable on the draggable to support revert: 'valid/invalid'
                  if(this.shouldRevert) this.instance.options.revert = true;
  
                  //Trigger the stop of the sortable
                  this.instance._mouseStop(event);
  
                  this.instance.options.helper = this.instance.options._helper;
  
                  //If the helper has been the original item, restore properties in the sortable
                  if(inst.options.helper == 'original')
                      this.instance.currentItem.css({ top: 'auto', left: 'auto' });
  
              } else {
                  this.instance.cancelHelperRemoval = false; //Remove the helper in the sortable instance
                  this.instance._trigger("deactivate", event, uiSortable);
              }
  
          });
  
      },
      drag: function(event, ui) {
  
          var inst = $(this).data("draggable"), self = this;
  
          $.each(inst.sortables, function(i) {
              
              //Copy over some variables to allow calling the sortable's native _intersectsWith
              this.instance.positionAbs = inst.positionAbs;
              this.instance.helperProportions = inst.helperProportions;
              this.instance.offset.click = inst.offset.click;
              
              if(this.instance._intersectsWith(this.instance.containerCache)) {
  
                  //If it intersects, we use a little isOver variable and set it once, so our move-in stuff gets fired only once
                  if(!this.instance.isOver) {
  
                      this.instance.isOver = 1;
                      //Now we fake the start of dragging for the sortable instance,
                      //by cloning the list group item, appending it to the sortable and using it as inst.currentItem
                      //We can then fire the start event of the sortable with our passed browser event, and our own helper (so it doesn't create a new one)
                      this.instance.currentItem = inst.options.morph(inst)
                          .appendTo(this.instance.element).data("sortable-item", true);
                      this.instance.options._helper = this.instance.options.helper; //Store helper option to later restore it
                      this.instance.options.helper = function() { return ui.helper[0]; };
  
                      event.target = this.instance.currentItem[0];
                      this.instance._mouseCapture(event, true);
                      this.instance._mouseStart(event, true, true);
  
                      //Because the browser event is way off the new appended portlet, we modify a couple of variables to reflect the changes
                      this.instance.offset.click.top = inst.offset.click.top;
                      this.instance.offset.click.left = inst.offset.click.left;
                      this.instance.offset.parent.left -= inst.offset.parent.left - this.instance.offset.parent.left;
                      this.instance.offset.parent.top -= inst.offset.parent.top - this.instance.offset.parent.top;
  
                      inst._trigger("toSortable", event);
                      inst.dropped = this.instance.element; //draggable revert needs that
                      //hack so receive/update callbacks work (mostly)
                      inst.currentItem = inst.element;
                      this.instance.fromOutside = inst;
  
                  }
  
                  //Provided we did all the previous steps, we can fire the drag event of the sortable on every draggable drag, when it intersects with the sortable
                  if(this.instance.currentItem) this.instance._mouseDrag(event);
  
              } else {
  
                  //If it doesn't intersect with the sortable, and it intersected before,
                  //we fake the drag stop of the sortable, but make sure it doesn't remove the helper by using cancelHelperRemoval
                  if(this.instance.isOver) {
  
                      this.instance.isOver = 0;
                      this.instance.cancelHelperRemoval = true;
                      
                      //Prevent reverting on this forced stop
                      this.instance.options.revert = false;
                      
                      // The out event needs to be triggered independently
                      this.instance._trigger('out', event, this.instance._uiHash(this.instance));
                      
                      this.instance._mouseStop(event, true);
                      this.instance.options.helper = this.instance.options._helper;
  
                      //Now we remove our currentItem, the list group clone again, and the placeholder, and animate the helper back to it's original size
                      this.instance.currentItem.remove();
                      if(this.instance.placeholder) this.instance.placeholder.remove();
  
                      inst._trigger("fromSortable", event);
                      inst.dropped = false; //draggable revert needs that
                  }
  
              };
  
          });
  
      }
  });
        
        
        var widget;
        $accordion.find('button').draggable({
          connectMorphSortable: $layoutContent,
          morph: function (draggable) {
            widget = createWidget($(draggable.element).find('.ui-button-text').text());
            return widget;
          },
          helper: function () {
            return createWidget($(this).find('.ui-button-text').text());
          },
          cancel: false,
          revert: "invalid",
          scroll: 'true',
          scrollSpeed: 40,
          appendTo: 'body',
          //toSortable: function (event,ui) {
          //  //$(ui.helper).css('border', '1px solid red');
          //  var sortable = $layoutContent.data('sortable');
          //  console.log(sortable.currentItem);
          //  sortable.currentItem = widget;
          //  sortable.currentItem
          //    .css('width','550px')
          //    .data("sortable-item", true)
          //    .appendTo(sortable.element)
          //  ;
          //  
          //  sortable._storedCSS = []; // fix, weil draggable den helper von sortable ja wegreplaced (und der diese variable setzt)
          //}
        });
        
        $layoutContent.sortable({
          revert: true,
          cancel: false
        });
        
    
        $layoutContent.droppable({
          hoverClass: 'hover',
          drop: function (event,ui) {
            $layoutContent.trigger('unsaved');
          }
        });
      }, 2000);
    } else {
      $.psc.reject('#content was not found');
    }
  });
});