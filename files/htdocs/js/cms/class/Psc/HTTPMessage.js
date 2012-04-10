Class('Psc.HTTPMessage', {
  
  has: {
    header: { is : 'rw', required: false, isPrivate: true, init: Joose.I.Object }
  },

  methods: {
    setHeaderField: function(key, value) {
      this.getHeader()[ key ] = value;
      return this;
    },

    getHeaderField: function(key) {
      return this.getHeader()[key] || null;
    },
    
    removeHeaderField: function(key) {
      delete this.getHeader()[key];
    },
    
    parseHeader: function (headers) {
      // copy von jquery
      var rheaders = /^(.*?):[ \t]*([^\r\n]*)\r?$/mg; // IE leaves an \r character at EOL
      this.setHeader({});
	  while((m = rheaders.exec(headers))) {
		this.setHeaderField(m[1], m[2]);
	  }
      
      return this;
    }
  }
});