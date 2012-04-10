Class('Psc.ResponseMetaReader', {
  
  use: ['Psc.Code', 'Psc.InvalidArgumentException'],
  
  has: {
    response: { is : 'r', required: true, isPrivate: true },
    data: { is : 'r', required: false, isPrivate: false }
  },
  
  after: {
    initialize: function(props) {
      if (this.getResponse()) {
        this.data = $.parseJSON(this.getResponse().getHeaderField('X-Psc-Cms-Meta')) || {};
      }
    }
  },

  methods: {
    get: function(keys) {
      if (!Psc.Code.isArray(keys)) {
        throw new Psc.InvalidArgumentException('keys','Array');
      }
      
      var data = this.getData();
      
      if (keys.length === 0) {
        return data;
      }
      
      for (var i = 0; i < keys.length; i++) {
        // sub-array can be empty
        if (!data || typeof(data) !== 'object') {
          return null;
        }
        
        var key = keys[i];
        // sub-array has not the requested key
        if (!data[key]) {
          return null;
        }
        
        // goto sub-array => goto next or return sub-array
        data = data[key];
      }
        
      return data;
    },
    
    has: function (keys) {
      return this.get(keys) !== null;
    },
    
    toString: function() {
      return "[Psc.ResponseMetaReader]";
    }
  }
});