Class('Psc.AjaxFormHandler', {
  isa: 'Psc.AjaxHandler',
  
  use: ['Psc.AjaxException', 'Psc.Exception'],

  has: {
    //form: { is : 'rw', required: true, isPrivate: true } // jquery form Element
  },

  methods: {
    handle: function(formRequest) {
      var that = this;
      var d = $.Deferred();
      var $form = formRequest.getForm();
      var options = this.getAjaxOptions(formRequest);
      
      options.success = function (convertedData, textStatus, jqXHR) {
        var response = that.createResponseFromXHR(jqXHR);
        response.setBody(convertedData);
        
        that.processResponse(response, d);
      };
      
      options.error = function(jqXHR, textStatus, error) {
        if (textStatus === 'parsererror') {
          throw new Psc.AjaxException(textStatus, "Interner Fehler beim AjaxRequest. Request ist nicht konsistent! Sind Psc.Response und Psc.request.getFormat() konsistent?");
        } else if (textStatus === 'error') {
          
          that.processResponse(that.createResponseFromXHR(jqXHR), d);

        } else {
          throw new Psc.Exception('Unbekannter TextStatus: '+textStatus);
        }
      };
      
      // submit
      $form.ajaxSubmit(options);
      
      return d.promise();
    },
    
    getAjaxOptions: function(formRequest) {
      var options = this.SUPER(formRequest);
      // vll mal so method checken oder url deleten oder so?
      
      return options;
    },
    toString: function() {
      return "[Psc.AjaxFormHandler]";
    }
  }
});