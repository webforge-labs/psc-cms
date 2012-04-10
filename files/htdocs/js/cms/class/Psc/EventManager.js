Class('Psc.EventManager', {
  
  has: {
    //attribute1: { is : 'rw', required: false, isPrivate: true }
    jq: { is: 'r', required: false, handles: [ 'bind', 'on', 'off', 'trigger' ] }
  },
  
  after: {
    initialize: function () {
      // nicht init: für attribute jq benutzen, denn das geht nicht richtig - bindet dann an window oder so
      this.jq = $(this);
    }
  },
  
  methods: {
    /*
      bevor ich hier mit den komischen event namespaces von jQuery hier rum mache, gebe ich die API einfach weiter (first make it work ....)
      man kann überprüfen mit hasEvent ob das event zum EventManager gehört(e)
      
      die einträge von data werden in das event objekt kopiert (dies ist kein eintrag von data)
    */
    createEvent: function (name, data) {
      data = data || {}
      data['isPscEvent'] = true;
      return jQuery.Event(name, data);
    },
    
    hasEvent: function (event) {
      return event.isPscEvent;
    },

    /**
     * @param object eventData
     * @param array handlerData
     */
    triggerEvent: function(name, eventData, handlerData) {
      return this.trigger(
        this.createEvent(name, eventData),
        handlerData
      );
    },
    toString: function() {
      return "[Psc.EventManager]";
    }
  }
});