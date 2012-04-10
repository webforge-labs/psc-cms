Class('Psc.InvalidArgumentException', {
  isa: 'Psc.Exception',
  
  use: ['Psc.Code'],

  has: {
    arg: { is : 'rw', required: true },
    expected: { is : 'rw', required: true },
    actual: { is: 'rw', required: false }
  },
  
  methods: {
    BUILD: function (arg, expected, actual) {
      var msg = "Falscher Parameter für Argument: '"+arg+"'. Erwartet wird: "+expected;
      
      if (arguments.length >= 3) {
        msg += 'Übergeben wurde: '+Psc.Code.varInfo(actual);
      }
      
      return Joose.O.extend(this.SUPER(msg), {
        arg: arg,
        expected: expected,
        actual: actual
      });
    }
  }
});
