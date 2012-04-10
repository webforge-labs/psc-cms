(function( $ ){
  
  $.widget( "pscUI.tabsContentItem", {
    
    options: {
      identifier: null,
      type: null,
      data: {},
      drag: false,
      formName: null,
      draggableOptions: {},
      url: null,
      label: null,
      fullLabel: null
    },

    _create: function() {
      var o = this.options,
          self = this
      ;
      
      self.fullLabeled = false;
      
      if (o.disabled == true) {
        this.disable();
      }

      /* defaults für draggable */
      if (o.drag == true) {
        this.draggableOptions = $.extend({}, {
          cancel: false,
          revert: false,
          helper: 'clone',
          scroll: 'true',
          scrollSpeed: 40,
          appendTo: 'body'
        }, o.draggableOptions);
      }
      
      this.formName = o.formName || this.element.attr('id');      
      
      this.element
        .addClass('tabs-content-item')
        .click(function (e) {
          /* morph label into full label or vice versa */
          if (e.ctrlKey && o.fullLabel) {
            e.preventDefault();
            e.stopPropagation();
            var fin = 600, fout = 400;
            
            if (!self.fullLabeled) {
              self.element.fadeOut(fout,'easeInOutExpo', function () {
                self.element.button('option','label',  o.fullLabel);
                self.element.fadeIn(fin);
                self.fullLabeled = true;
              });
            } else {
              self.element.fadeOut(fout, 'easeInOutExpo', function () {
                self.element.button('option','label', o.label);
                self.element.fadeIn(fin);
                self.fullLabeled = false;  
              });
            }
          }
        });
      ;
      
      if (o.drag) {
        this.element
          .draggable(this.draggableOptions)
          .addClass('drag-item')
        ;
      }
    },
    
    openTab: function () {
      // deprecated, in API2 connected sich main selbst mit dem TCI
      $.pscUI('tabs','ui.openContent', null, this.tabsId(), this.options.url, this.options.label);
    },
    
    // wird von main aufgerufen, wenn das item in die tabs-leiste gezogen wird
    getTabProperties: function() {
      return {
        id: this.tabsId(),
        url: this.options.url,
        label: this.options.label
      };
    },
    
    getIdentifier: function() {
      return this.options.identifier;
    },

    getType: function() {
      return this.options.type;
    },
    
    getFormName: function () {
      return this.formName;
    },
    
    getExport: function () {
      return {
        type: this.options.type,
        data: this.options.data,
        identifier: this.options.identifier
      };
    },
    
    getURL: function () {
      return this.options.url;
    },
    
    getLabel: function () {
      return this.options.label;
    },
    
    enable: function() {
      
      this.element.button('enable');
	  return this._setOption( "disabled", false );
	},

    disable: function() {
      this.element.button('disabled');
      
	  return this._setOption( "disabled", true );
	},
    
    tabsId: function () {
      return this.options.type+'-'+this.options.identifier;
    },

	_setOption: function( key, value ) {
		this.options[ key ] = value;
        
        if (key === 'drag') {
          if (value) {
            this.widget().draggable(this.draggableOptions)
              .addClass('drag-item');
          } else {
            this.widget().draggable('destroy')
              .removeClass('drag-item');
          }
        }

		if ( key === "disabled" ) {
			this.widget()
				[ value ? "addClass" : "removeClass"](
					this.widgetBaseClass + "-disabled" + " " +
					"ui-state-disabled" )
				.attr( "aria-disabled", value );
		}

		return this;
	}
  });
})( jQuery );