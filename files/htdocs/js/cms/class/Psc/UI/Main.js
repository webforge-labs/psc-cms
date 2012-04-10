Class('Psc.UI.Main', {
  
  use: [
    'Psc.EventManager', 'Psc.AjaxFormHandler', 'Psc.FormRequest', 'Psc.ResponseMetaReader',
    'Psc.InvalidArgumentException', 'Psc.Exception', 'Psc.Code',
    'Psc.UI.Tabs', 'Psc.UI.Tab', 'Psc.UI.Spinner',
    'Psc.UI.EffectsManager'
  ],
  
  has: {
    eventManager: { is : 'rw', required: false, isPrivate: true },
    ajaxFormHandler: { is : 'rw', required: false, isPrivate: true },
    contextMenuManager: { is : 'rw', required: false, isPrivate: true },
    effectsManager: { is : 'rw', required: false, isPrivate: true },
    spinner: { is : 'rw', required: false, isPrivate: true },
    tabs: { is: 'rw', required: true, isPrivate: true }
  },
  
  after: {
    initialize: function (props) {
      if (!props.eventManager) {
        this.$$eventManager = new Psc.EventManager();
      }

      if (!props.ajaxFormHandler) {
        this.$$ajaxFormHandler = new Psc.AjaxFormHandler();
      }

      if (!props.effectsManager) {
        this.$$effectsManager = new Psc.UI.EffectsManager();
      }

      if (!props.spinner) {
        this.$$spinner = new Psc.UI.Spinner();
      }
      
      if (!props.contextMenuManager) {
        this.$$contextMenuManager = new Psc.UI.ContextMenuManager();
      }
      
      if (Psc.Code.isInstanceOf(props.tabs, Psc.UI.Tabs)) {
        this.initTabs();
      } else {
        throw new Psc.InvalidArgumentException('tabs','Objekt: Psc.UI.Tabs');
      }
    }
  },

  methods: {
    /**
     * Speichert den aktuellen Tab
     * 
     * wenn tabHook false zurückgibt wird der neue tab nicht geöffnet
     */
    save: function ($form, tabHook) {
      var controller = new Psc.UI.FormController({ form: $form, ajaxFormHandler: this.$$ajaxFormHandler, eventManager: this.$$eventManager });
      constroller.save(tabHook);
    },

    attachHandlers: function() {
      var that = this;
      var eventManager = this.$$eventManager;
      var tabs = this.getTabs();
      
      eventManager.on('tab-open', function(e, tab, $target) {
        // $target kann auch ein tci sein
        if (e.source === 'tabsContentItem')  {
          tabs.open(tab, $target.element);
        } else {
          tabs.open(tab, $target);
        }
      });
      
      eventManager.on('tab-tci-open', function(e, tci) {
        var tab = new Psc.UI.Tab( tci.getTabProperties() );
        
        eventManager.trigger(
          eventManager.createEvent('tab-open', { source: 'tabsContentItem' }),
          [tab, tci]
        );
      });

      eventManager.on('tab-close', function(e, tab, $target) {
        tabs.close(tab);
      });
      
      eventManager.on('tab-unsaved', function(e, tab, $target) {
        tabs.unsaved(tab);
      });

      eventManager.on('form-saved', function(e, $form, metaResponse, tabHook) {
        /* bearbeite tabs-meta */
        var open = true;
        var meta;
        
        if (Psc.Code.isInstanceOf(metaResponse, Psc.Response)) {
          meta = new Psc.ResponseMetaReader({response: metaResponse});
        } else if (Psc.Code.isInstanceOf(metaResponse, Psc.ResponseMetaReader)) {
          meta = metaResponse;
        } else {
          throw new Psc.InvalidArgumentException('event:form-saved:metaResponse','Psc.ResponseMetaReader', metaResponse);
        }
        
        if (tabHook) {
          open = tabHook();
        }
        
        if (meta.get(['data','tab','close']) === true) {
          $form.trigger('close'); // this bubbles up and triggers tab-close with the tab correctly
          
        } else if (open !== false && meta.has(['data','tab'])) {
          
          /* neuen Tab öffnen */
          var tab = new Psc.UI.Tab(
            meta.get(['data','tab'])
          );
          
          eventManager.triggerEvent('tab-open', {}, [tab, $form]); // use form as target
        }
      });
    },
    
    /**
     * Attached alle Handler zu den Tabs, die zum "globalen" Ganzen gehören
     *
     * - elemente in den tabs können "unsaved", "reload", "close" und "saved" triggern
     * - die tabs-nav ist ein droppable in welches content-items hineingezogen werden können
     *
     * Buttons (save, reload, save-close) triggern ein weiteres event, welches dann zur form (bei reload zum panel) hochbubbelt. Erst da delegieren wir an main weiter
     */
    initTabs: function() {
      var that = this;
      var eventManager = this.getEventManager();
      var $tabs = this.$$tabs.unwrap();
      
      // dependency inject
      this.$$tabs.setContextMenuManager(this.$$contextMenuManager);
      
      /*
        Tab-Events
       
        Elemente in tab-panels können diese events triggern, diese werden dann an den eventManager weitergeleitet
        es wird der Tab genommen in dem sich das Element welches das Event-triggered genommen
      */
      $tabs.on('unsaved reload close saved', 'div.ui-tabs-panel', function(e) {
        var $tab = $(this), $target = $(e.target);
        e.preventDefault();
        e.stopPropagation();
        
        // transform $tab to tab
        var tab = that.getTabs().tab({ id: $tab.attr('id') });
        
        eventManager.trigger(
          eventManager.createEvent('tab-'+e.type), [tab, $target]
        );
        
        e.stopPropagation();
      });
      
      /* Form-Events */
      /* EntityForm + Normale Form Event-Binds (an Buttons und Elemente)
         div.psc-cms-ui-form (div.psc-cms-ui-entity-form)
         jede entity-form ist auch eine form
      */
      $tabs.on('save save-close','div.psc-cms-ui-form', function(e) {
        var $form = $(this), $target = $(e.target);
        e.preventDefault();
        e.stopPropagation();
      
        eventManager.trigger(
          eventManager.createEvent('form-'+e.type), [$form, $target]
        );
      });

      /* Reload-Button */
      $tabs.on('click','div.psc-cms-ui-form button.psc-cms-ui-button-reload', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).trigger('reload'); // bubble up to tabs on event
      });
      
      /* Speichern + Schliessen - Button */
      $tabs.on('click','div.psc-cms-ui-form button.psc-cms-ui-button-save-close', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).trigger('save-close'); // bubble up to form
      });
      
      /* Speichern - Button */
      $tabs.on('click','div.psc-cms-ui-form button.psc-cms-ui-button-save', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).trigger('save');
      });
    
      /*
        Formular Komponenten die verändert werden, markieren das Formular als "unsaved"
        dies ist im moment eine sehr naive Variante (lots of false-positive)
      */
      $tabs.on('change keyup','div.psc-cms-ui-form:not(.unbind-unsaved) (input, textarea, select):not([readonly="readonly"])', function(e) {
        $(this).trigger('unsaved'); // hochbubblen bis zur form
      });

      /* TCI auf die Navi Leiste ziehen */
      $tabs.find('ul.ui-tabs-nav').droppable({
        hoverClass: 'hover',
        drop: function (event, ui) {
          var item = ui.draggable.data('tabsContentItem');
          
          eventManager.trigger(
            eventManager.createEvent('tab-tci-open'), [item]
          );
        }
      });
      
      /* Formular Gruppen klappbar machen */
      $tabs.on('click', 'legend.collapsible', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var $target = $(e.target), $fieldset = $target.parent('fieldset');
      
        if ($fieldset.length && $fieldset.is('.psc-cms-ui-group')) {
          $fieldset.find('div.content:first').toggle();
        }
      });

      // links als psc-cms-ui-tabs-item
      $tabs.on('click', 'a.psc-cms-ui-tabs-item', function (e) {
        e.preventDefault();
        e.stopPropagation();
      
        var $target = $(e.target), id = Psc_getGUID($target);
        
        if (id != null) {
          var tab = new Psc.UI.Tab({
            id: id,
            label: $target.text(),
            url: $target.attr('href')
          });
          
          eventManager.trigger(
            eventManager.createEvent('tab-open', { source: 'anchor' }),
            [tab, $target]
          );
        } else {
          throw new Psc.Exception('guid ist nicht gesetzt von '+$target.html());
        }
      });
      
      /* Wenn ein TCI bis hier hochbubbelt, haben wir es bereits bearbeitet oder es macht nichts
        dann wollen wir vermeiden, dass es die Seite neulädt */
      $tabs.on('click','div.psc-cms-ui-form button.tabs-content-item', function(e) {
        if (e.isDefaultPrevented()) return;
  
        e.preventDefault();
      });
      
     /*
      * close all contextMenus when clicked on body and still open
      */
      $('body').on('click', function (e) {
        that.getContextMenuManager().closeAll();
      });
    },
    toString: function() {
      return "[Psc.UI.Main]";
    }
  }
});