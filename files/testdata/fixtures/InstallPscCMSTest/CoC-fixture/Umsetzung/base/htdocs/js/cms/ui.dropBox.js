(function( $ ){
  
  $.widget( "pscUI.dropBox", {
    
    options: {
      multiple: true,
      formName: null,
      itemType: null,
      verticalButtons: false,
      droppableOptions: {}
    },

    _create: function() {
      var o = this.options, self = this;
      o.formName = o.formName || this.element.attr('id');
      
      this.droppableOptions = $.extend({}, {
        hoverClass: 'hover',
        drop: function (e, ui) {
          if (ui.helper.data('pscsort') == true) return;
          
          var opts = $.extend({}, ui.draggable.tabsContentItem('option'), { drag: false });
          var $button = $('<button></button>')
                        .tabsContentItem( opts )
                        .button({label: opts.label })
                      ;
          
          self.element.trigger('unsaved');
          self.appendButton($button);
        }
      }, o.droppableOptions);
      
      this.element.droppable(this.droppableOptions);

      /* gesetzte elemente hashen */
      this.hashMap = {};
      this.element.find('button').each(function () {
        self.hashMap[ self.hashButton($(this)) ] = true;
      });
      
      this.element.click(function (e) {
        var $target = $(e.target);
        
        if (e.target.nodeName == 'SPAN' && $target.hasClass('ui-button-text')) { // fix für chrome
          $target = $target.parent('button');
        }
        
        if ($target.is('button')) {
          e.preventDefault();
          e.stopPropagation();
          
          if (o.disabled == true) return;
          
          self.hashMap[self.hashButton($target)] = false; // hash
          $target.remove();
        }
      });

      if (o.disabled == true) {
        this.disable(); 
      }
    },
    appendButton: function ($button) {
      if (this.options.disabled == true) return;
      
      $button.tabsContentItem('option','drag',false); // macht draggable auch weg
      
      if (!this.options.multiple && this.hasButton($button)) {
        this._trigger('exists', null, {button: $button});
        return;
      }
      
      
      this.hashMap[ this.hashButton($button) ] = true;
      this.element.append($button);
    },
    
    hasButton: function ($button) {
      var ident = this.hashButton($button);
      
      return this.hashMap.hasOwnProperty(ident) && this.hashMap[ident] === true;
    },
    
    hashButton: function($button) {
      return $button.tabsContentItem('getType')+'-'+$button.tabsContentItem('getIdentifier');
    },
    
    /* wird von pscUI.form.save aufgerufen */
    serialize: function(data) {
      var o = this.options; // hier referenzen da wir this im each verlieren
      
      // keine daten wenn disabled
      if (o.disabled == true) return data;
      
      var json = [];
      
      this.element.find('button').each(function (i) {
        var $item = $(this).data('tabsContentItem');

        json[i] = $item.getIdentifier();
      });
      
      data[o.formName] = $.toJSON(json);
      
      return data;
    },
    
    enable: function() {
      this.element.find('button').each(function (i) {
        $(this).tabsContentItem('enable');
      });
      
      this.element.sortable('enable');
      this.element.droppable('enable');
	  return this._setOption( "disabled", false );
	},
    
	disable: function() {
      this.element.find('button').each(function (i) {
        $(this).tabsContentItem('disable');
      });
	  
      this.element.sortable('disable');
      this.element.droppable('disable');
      return this._setOption( "disabled", true );
	}
  });

})( jQuery );