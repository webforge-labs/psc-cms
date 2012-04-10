Class('Psc.Exception', {
  
  has: {
    message: { is : 'rw', required: true },
    name: { is : 'rw', required: true, init: 'Exception' }
  },
  
  methods: {
    BUILD: function (message, name) {
      if (!name) {
        name = this.meta.name;
      }
      var props = this.SUPER();
          props.name =  name;
          props.message = message;


      // leider ist der trace nicht 100% genau, da wir ja nicht unbedingt throwen (und auch catchen können), wenn wir gerade constructen  :o
      if (console.trace) {
        //console.trace();
      }
      
      return props;
    },
    toString: function() {
      return "["+this.getName()+" with Message '"+this.getMessage()+"']";
    }
  }
});