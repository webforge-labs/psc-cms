Class('Psc.UI.Tab', {
  
  has: {
    content: { is : 'rw', required: false, isPrivate: true, init: null },
    id: { is : 'rw', required: true, isPrivate: true },
    url: { is : 'rw', required: true, isPrivate: true },
    label: { is : 'rw', required: true, isPrivate: true },
    unsaved: { is : 'rw', required: false, isPrivate: true, init: false },
    closable: { is: 'rw', required: false, isPrivate: true, init: true }
  },

  methods: {
    isUnsaved: function() {
      return this.$$unsaved;
    },
    isClosable: function() {
      return this.$$closable;
    },
    toString: function() {
      return "[Psc.UI.Tab]";
    }
  }
});