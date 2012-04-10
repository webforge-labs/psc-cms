use(['Psc.WrongValueException'], function () {
  module("Psc.WrongValueException");
  
  test("construct", function() {
    
    var e = new Psc.WrongValueException('Wert: xyz nicht erlaubt. Erlaubt sind nur abc');
    
    equal(e.getName(), "Psc.WrongValueException");
    equal(e.getMessage(), 'Wert: xyz nicht erlaubt. Erlaubt sind nur abc');
    
  });
});