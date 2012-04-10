Class('Psc.Response', {
  isa: 'Psc.HTTPMessage',

  has: {
    code: { is : 'rw', required: true },
    reason: { is : 'rw', required: false, init: null },
    body: { is : 'rw', required: false, init: null }
    // headers die headers als string (werden dann geparsed)
  },
  
  after: {
    initialize: function (props) {
      if (props.headers) {
        this.parseHeader(props.headers);
      }
      
      if (typeof(this.getCode()) === 'string') {
        this.setCode(parseInt(this.getCode(), 10));
      }
    }
  },

  methods: {
    toString: function () {
      return '[Psc.Response Code: '+this.getCode()+']'
    },
    
    isValidation: function() {
      return this.getHeaderField('X-Psc-Cms-Validation') === 'true';
    }
  }
});