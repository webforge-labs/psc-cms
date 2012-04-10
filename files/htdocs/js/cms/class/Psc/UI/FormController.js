Class('Psc.UI.FormController', {
  
  use: ['Psc.EventManager', 'Psc.AjaxFormHandler', 'Psc.FormRequest'],
  
  has: {
    form: { is : 'rw', required: true, isPrivate: true },
    ajaxFormHandler: { is : 'rw', required: false, isPrivate: true },
    eventManager: { is : 'rw', required: false, isPrivate: true }
  },
  
  after: {
    initialize: function (props) {
      if (!props.eventManager) {
        this.$$eventManager = new Psc.EventManager();
      }

      if (!props.ajaxFormHandler) {
        this.$$ajaxFormHandler = new Psc.AjaxFormHandler();
      }
    }
  },

  methods: {
    /**
     * Speichert den aktuellen Tab
     * 
     * wenn tabHook false zurückgibt wird der neue tab nicht geöffnet
     */
    save: function (tabHook) {
      /* wir holen uns alle items aus dem formular mit den nötigen informationen und bauen daraus ein paket welches wir per ajax posten */
      var formRequest = new Psc.FormRequest({form: this.$$form});
      
      formRequest.setBody(this.serialize());
      
      this.$$ajaxFormHandler.handle(formRequest)
        .done(function (response) {
          this.$$eventManager.trigger(
            this.$$eventManager.createEvent('form-saved'),
            [this.$$form, response, tabHook]
          );
        })
        .fail(function (response) {
          this.$$eventManager.trigger(
            this.$$eventManager.createEvent('error-form-save'),
            [this.$$form, response]
          );
        });
    },
    serialize: function () {
      var data = {};
            
      /* buttons in dropboxen holen */
      this.$$form.find('div.psc-cms-ui-drop-box').each(function () {
        $(this).dropBox('serialize', data);
      });
      
      /* Comboboxen im Select Modus holen */
      this.$$form.find('input.psc-cms-ui-combo-box').each(function () {
        $(this).comboBox('serialize', data);
      });
      
      return data;
    },
    toString: function() {
      return "[Psc.UI.FormController]";
    }
  }
});