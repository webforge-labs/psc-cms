/**
 * Default: Denys all Events to be triggered
 *
 * only those in array: allow will be allowed
 *
 * needs QUnit! loaded
 */
Class('Psc.EventManagerMock', {
  isa: 'Psc.EventManager',

  has: {
    triggered: { is : 'rw', required: false, isPrivate: false, init: Joose.I.Object },
    allow: { is: 'rw', required: true, init: Joose.I.Array }, // list of event types to be denied when triggered
    denySilent: { is: 'rw', required: true, init: false }
  },
  
  after: {
    initialize: function () {
      this.allow = $.makeArray(this.allow);
      this.denySilent = !!this.denySilent;
    }
  },
  
  override: {
    trigger: function (event, handlerData) {
      if (!this.triggered[event.type]) {
        this.triggered[event.type] = [];
      }
      
      handlerData = handlerData || [];
      this.triggered[event.type].push({event: event, data: handlerData});
       
      if (this.isAllowed(event)) {
        this.SUPERARG(arguments);
      }
    }
  },

  methods: {
    isAllowed: function (event) {
      if (this.allow && $.inArray(event.type, this.allow) >= 0) {
        return true;
      }
      
      if (this.denySilent) {
        return false;
      } else {
        throw new Psc.Exception('Es wird erwartet, dass Event '+event.type+' nie aufgerufen wird');
      } 
    },
    
    /**
     * @param string eventType
     * @param assert kann eine nummer sein für die verifizierung der anzahl der events (returns true/false)
     *               ODER kann eine funktion sein, die wie ein event handler das event und die handlerData übergeben bekommt. gibt diese true zurück wird das entsprechende event zurückgegeben und die schleife abgebrochen
     * @param assert2 kann eine funktion sein, wenn assert eine anzahl der events ist und fungiert dann ebenfalls als callback zum verifzieren
     */
    wasTriggered: function(eventType, assert, assert2) {
      var verify;
      if ($.isNumeric(assert)) {
        if (!this.wasTriggeredTimes(eventType, assert)) { // check first
          return false;
        }
        
        if ($.isFunction(assert2)) {
          verify = function(tr) {
            var args = [tr.event].concat(tr.data);
            return assert2.apply(tr.event, args);
          };
        } else {
          return true; // da times ok ist
        }
      } else if ($.isFunction(assert)) {
        verify = function(tr) {
          var args = [tr.event].concat(tr.data);
          return assert.apply(tr.event, args);
        };
      } else {
        handlerData = assert || [];
        verify = function(tr) {
          return QUnit.equiv(tr.data, handlerData);
        }
      }
      
      var triggered = this.getTriggered();
      var events;
      if ($.isArray(events = triggered[eventType])) {
        var tr, l = events.length, i;
        for (i = 0; i < l; i++) {
          tr = events[i];
          if (verify(tr)) {
            return tr.event;
          }
        }
      }

      return false;
    },
    wasTriggeredTimes: function(eventType, num) {
      var triggered = this.getTriggered();
      var events;
      
      if (num === 0) {
        return !$.isArray(triggered[eventType]);
      } else {
        return $.isArray(events = triggered[eventType]) && events.length === num;
      }
    },
    toString: function() {
      return "[Psc.EventManagerMock]";
    }
  }
});