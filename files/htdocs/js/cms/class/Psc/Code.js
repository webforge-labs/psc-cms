Class('Psc.Code', {
  
  use: ['Psc.WrongValueException', 'Psc.Exception'],
  
  my: {
    
    methods: {
      value: function () {
        var values = (Array.prototype.slice).apply(arguments), value = values.shift();
        
        if (Joose.A.exists(values, value)) {
          return value;
        } else {
          throw new Psc.WrongValueException("Wert '"+Psc.Code.varInfo(value)+"' ist nicht erlaubt");
        }
      },
      
      isInstanceOf: function(obj, constructor) {
        if (!Joose.O.isClass(constructor)) {
          throw new Psc.Exception('isInstanceOf parameter #2 muss ein Constructor einer Joose Class sein. '+Psc.Code.varInfo(constructor)+' wurde übergeben');
        }
        
        if (Joose.O.isInstance(obj) && (obj instanceof constructor)) { // joose.o. gibt hier undefined zurück deshalb "casten" wir zu bool
          return true;
        }
        
        return false;
      },
      
      odump: function(object) {
        return $.toJSON(object); 
      },
      varInfo: function (variable) {
        if (variable instanceof Object) {
          return Psc.Code.odump(variable);
        } else {
          return variable;
        }
      },
      
      isArray: function (variable) {
        return Object.prototype.toString.apply(variable) === '[object Array]';
      }
    }
  }
});