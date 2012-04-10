Class('Psc.Request', {
  isa: 'Psc.HTTPMessage',
  
  use: ['Psc.Code'],
  
  has: {
    url: { is : 'rw', required: true, isPrivate: true },
    method: { is : 'rw', required: true, isPrivate: true },
    body: { is : 'rw', required: false, isPrivate: true },
    format: { is : 'rw', required: false, isPrivate: true, init: 'json' }
  },
  
  override: {
    setMethod: function (method) {
      Psc.Code.value(method, 'POST', 'GET', 'PUT', 'DELETE');
      this.SUPER(method);

      if (method != 'POST' && method != 'GET') {
        this.setHeaderField('X-Psc-Cms-Request-Method',method);
      } else {
        this.removeHeaderField('X-Psc-Cms-Request-Method');
      }
    },
    setFormat: function (format) {
      Psc.Code.value(format, 'html','json');

      this.SUPER(format);
    },
    initialize: function (props) {
      this.setMethod(props.method);
      
      if (props.format) { this.setFormat(props.format); }

      this.SUPER(props);
    }
  },
  
  methods: {
    toString: function() {
      return "[Psc.Request "+this.method+" "+this.url+"]";
    }
  }
});