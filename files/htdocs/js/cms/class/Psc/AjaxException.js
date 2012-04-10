Class('Psc.AjaxException', {
  isa: 'Psc.Exception',

  has: {
    textStatus: { is : 'rw', required: true, isPrivate: true }
  },

  methods: {
    BUILD: function(textStatus, message) {
      return Joose.O.extend(this.SUPER(message), {
        textStatus: textStatus
      });
    },
    toString: function() {
      return "[Psc.AjaxException textStatus '"+this.getTextStatus()+"' '"+this.getMessage()+"']";
    }
  }
});