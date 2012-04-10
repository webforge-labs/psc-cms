(function( $ ) {
  $.widget( "ui.comboBox", {
    
    options: {
      width: null,
      ajaxData: {},
      ajax: true,
      itemType: 'unknown',
      itemData: {},
      delay: 500,
      minLength: 0,
      initialText: null,
      assignedItem: null,
      loadedItems: null,
      selectMode: false,
      formName: null
    },
    
    _create: function() {
      var self = this,
          o = this.options
      ;
      var input = this.input = this.element;

      this.initial = true;  // zeigt den initialText an, der auf Focus weggeht und initial umstellt
      this.assignedItem = o.assignedItem || null; // das ausgewählteItem (nach Click auf Menu, oder vorausgewählt
      this.loadedData = o.loadedItems || []; // data wenn ajax == false ist, oder schon ein ajax aufgerufen wurde
      this.lastTerm = null; // der term der zu loadedData passt (wenn ajax aktiv ist nur gesetzt)
      
      if (!o.formName) {
        o.formName = input.attr('name');
      }
      if (o.width) {
        input.css('width',o.width);
      }
      
      input
        .val( o.initialText || input.val() )
        .focus( function (e) {
          if (o.disabled) {
            e.preventDefault();
            input.blur();
            return false;
          }
          
          if (self.initial) {
            
            if (self.assignedItem == null) {
              input.val('');
            }

            input.removeClass('ui-state-disabled');
            
            self.initial = false;
          }
        })
        .autocomplete({
            delay: o.delay,
            disabled: o.disabled,
            minLength: o.minLength,
            source: function( request, response ) {
              
              if (self.lastTerm != null && request.term == self.lastTerm) {
                response( self.loadedData );
                return;
              }
              
              if (o.ajax) {
                
                $.ajax({
                  url : '/ajax.php?todo=ctrl&ctrlTodo=autoComplete',
                  type: 'POST',
                  global: false,
                  dataType: 'json',
                  data: $.extend({
                    'term': request.term,
                    'type': o.itemType,
                    'data': o.itemData
                    },
                    o.ajaxData
                  ),
                  success: function(data) {
                    if (data.status == 'ok') {
                      
                      self.loadedData = data.content.items;
                      
                      if (self.loadedData.length == 0) {
                        $.pscUI('effects','blink',input);
                      }
                      
                      self.lastTerm = request.term;
                      
                      response( self.loadedData );
                    }
                             
                    if (data.status == 'failure') {
                      alert(data.content);
                    }
                  }
                });
              } else {
                var matcher = new RegExp( $.ui.autocomplete.escapeRegex(request.term), "i" );
                var ldata = self.loadedData;
                
                response( $.map(self.loadedData, function(item) {
                  if (!request.term || matcher.test(item.label)) {
                    return item;
                  }
                }) );
              }
            },
            select: function( event, ui ) {
              // das ist das event, was bei klick oder tastatur action auf einen eintrag kommt
              
              self.assignedItem = ui.item;
              
              if ((!self.assignedItem.data || $.isEmptyObject(self.assignedItem.data)) && o.itemData) {
                self.assignedItem.data = o.itemData;
              }
              
              self._trigger( "selected", event, {
                item: self.assignedItem
              });
              
              event.preventDefault(); // nicht den text mit der value des items ersetzen, einfach den Suchstring lassen
              if (self.options.selectMode) {
                input.val(self.assignedItem.label);
              }
            }
        })
        .addClass( "ui-state-disabled ui-widget ui-widget-content ui-corner-left psc-cms-ui-combo-box" );

      //input.data( "autocomplete" )._renderItem = function( ul, item ) {
      //  return $( "<li></li>" )
      //          .data( "item.autocomplete", item )
      //          .append( "<a>" + item.label + "</a>" )
      //          .appendTo( ul );
      //};
      
      
      this.button = $( "<button type='button'>&nbsp;</button>" )
                    .attr( "tabIndex", -1 )
                    .attr( "title", "Suchergebnisse anzeigen" )
                    .insertAfter( input )
                    .button({
                        icons: {
                            primary: "ui-icon-triangle-1-s"
                        },
                        text: false,
                        disabled: o.disabled
                    })
                    .removeClass( "ui-corner-all" )
                    .addClass( "ui-corner-right ui-button-icon" )
                    .click(function() {
                        // nichts tun, falls schon ausgeklappt
                        if ( input.autocomplete( "widget" ).is( ":visible" ) ) {
                            return;
                        }

                        // work around a bug (likely same cause as #5265)
                        $( this ).blur();

                        //if(self.loadedData.length) {
                        //  input.data('autocomplete')._suggest(self.loadedData);
                        //} else {
                          input.autocomplete( "search", "" );
                        //}
                        input.focus();
                    });
    },
    
	enable: function() {
      this.button.button('enable');
      this.input.autocomplete('enable');
	  return this._setOption( "disabled", false );
	},
	disable: function() {
      this.button.button('disable');
      this.input.autocomplete('disable');
	  return this._setOption( "disabled", true );
	},

    destroy: function() {
      this.button.remove();
      $.Widget.prototype.destroy.call( this );
    },

    /* wird von pscUI.form.save aufgerufen */
    serialize: function(data) {
      var o = this.options; // hier referenzen da wir this im each verlieren
      var self = this;
      
      if (self.assignedItem != null) {
        data[o.formName] = self.assignedItem.value;
      }
      
      return data;
    }
  });
})( jQuery );