/**
 * Ein Wrapper für ein AutoComplete
 *
 * sendet mit einem ajaxHandler den Request an die übergebene URL
 *
 * @TODO delay,autoFoxus,minLength sollten direkt ans Widget delegiert werden (das müsste ja ganz gut mit handle gehen), das könnten wir im widgetWrapper machen
 */
Class('Psc.UI.AutoComplete', {
  isa: 'Psc.UI.WidgetWrapper',
  
  use: ['Psc.UI.EffectsManager','Psc.AjaxHandler','Psc.Request','Psc.Exception'],

  has: {
    autoFocus: { is : 'rw', required: false, isPrivate: true, init: false },
    delay: { is : 'rw', required: false, isPrivate: true, init: 300 },
    minLength: { is : 'rw', required: false, isPrivate: true, init: 2 },
    effectsManager: { is: 'rw', required: false, isPrivate: true },
    eventManager: { is : 'rw', required: false, isPrivate: true },
    url: { is: 'rw', required: true, isPrivate: true },
    ajaxHandler: { is: 'rw', required: false, isPrivate: true }
  },
  
  after: {
    initialize: function (props) {
      if (!props.eventManager) {
        this.$$eventManager = new Psc.EventManager();
      }
      
      if (!props.effectsManager) {
        this.$$effectsManager = new Psc.UI.EffectsManager();
      }
      
      if (!props.ajaxHandler) {
        this.$$ajaxHandler = new Psc.AjaxHandler();
      }
      
      this.checkWidget();
      this.initWidget();
    }
  },

  methods: {
    initWidget: function() {
      this.widget.autocomplete({
        delay: this.$$delay,
        minLength: this.$$minLength,
        autoFocus: this.$$autoFocus,
        source: this.getSourceHandler(),
        focus: function(e) {
          // prevent value inserted on focus
          return false;
        },
        select: this.getSelectHandler()
      });
      
      var autoComplete = this.widget.data('autocomplete');
      this.widget.on('keydown', function (e) {
        // don't navigate away from the field on tab when selecting an item
        if (e.keyCode === $.ui.keyCode.TAB && autoComplete.menu.active) {
          e.preventDefault();
        }
        
        if (e.keyCode === $.ui.keyCode.ENTER) {
          e.preventDefault();
        }
      });
    },    
    getSourceHandler: function () {
      var that = this;
      
      return function(acRequest, acResponse) {
        var acInput = that.unwrap();
        
        var request = new Psc.Request({
          url: that.getUrl(),
          method: 'GET',
          body: { search: acRequest.term},
          format: 'json'
        });
        
        that.getAjaxHandler().handle(request)
          .done(function (response) {
            var items = response.getBody();
            
            if (items.length == 0) {
              that.getEffectsManager().blink(acInput);
            }
            
            // items im menu anzeigen
            acResponse(items);
          })
          .fail(function (response) {
            alert('Leider ist ein unerwarteter Fehler aufgetreten '+response);
          });
      };
      
      // handling ohne ajax
      //function( request, response ) {
      //  var items = $.ui.autocomplete.filter(this.element.data('acData'), request.term);
      //                                
      //  if (items.length == 0) {
      //    $.pscUI('effects','blink',this.element);
      //  }
      //
      //  response(items);
      //}
    },
    getSelectHandler: function() {
      var that = this;
      var element = that.unwrap();
      
      return function (e, ui) {
        e.preventDefault();
        
        if (!ui.item) {
          throw new Psc.Exception('ui.item ist nicht gesetzt.');
        }
        
        if (ui.item.tab) {
          var tab = new Psc.UI.Tab(ui.item.tab);
        
          that.getEventManager().triggerEvent('tab-open', {source: 'autoComplete'}, [tab, element]);
        } else {
          throw new Psc.Exception('was anderes außer tab ist nicht implementiert: '+Psc.Code.varInfo(ui.item));
        }
      }
    },
    toString: function() {
      return "[Psc.UI.AutoComplete]";
    }
  }
});