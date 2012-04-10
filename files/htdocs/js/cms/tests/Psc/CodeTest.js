use(['Psc.Code','Psc.WrongValueException','Psc.InvalidArgumentException','Psc.Request'], function () {
  
  module("Psc.Code");
  
  test("valueReturnsFirstAndValidates", function() {
    assertEquals('a1', Psc.Code.value('a1', 'a1','a2','a3'));
    assertEquals('a3', Psc.Code.value('a3', 'a1','a2','a3'));
    assertEquals('a2', Psc.Code.value('a2', 'a1','a2','a3'));
  });
  
  test("valueThrowsExceptionOnWrongEntry", function () {
    assertException("Psc.WrongValueException", function () {
      Psc.Code.value('wrong', 'a1','a2','a3');
    });
    
  });
  
  test("varInfo", function() {
    assertEquals('a1', Psc.Code.varInfo('a1'));
  });
  
  test("isArray", function() {
    var positiveValues = [
      ['eins'],
      [{v: 1}, {v: 2}],
      []
    ];
    
    var negativeValues = [
      'eins',
      7,
      { most: 'important' },
      {},
      new Psc.InvalidArgumentException('katching','blubb')
    ];
    
    Joose.A.each(positiveValues, function (data) {
      assertTrue(Psc.Code.isArray(data), debug(data)+" isArray");
    });

    Joose.A.each(negativeValues, function (data) {
      assertFalse(Psc.Code.isArray(data), debug(data)+" !isArray");
    });
  });


  test("isInstanceOf", function() {
    var iae = new Psc.InvalidArgumentException('katching','blubb');
    var e = new Psc.Exception('wuah');
    var o = {};
    var a = [];
    
    var positiveValues = [
      [iae, Psc.InvalidArgumentException],
      [e, Psc.Exception]
    ];
    
    var negativeValues = [
      ['eins', Psc.InvalidArgumentException],
      [e, Psc.InvalidArgumentException],
      [7, Psc.Exception],
      [7, Psc.Request], // muss vorher geladen worden sein
      [{ most: 'important' }, Psc.InvalidArgumentException],
      [a, Psc.InvalidArgumentException],
      [o, Psc.InvalidArgumentException]
    ];
    
    var failureValues = [
      [{}, 'Psc.Exception'], // string ist nicht erlaubt
      [{}, 7]
    ];
    
    Joose.A.each(positiveValues, function (data) {
      assertTrue(Psc.Code.isInstanceOf(data[0], data[1]), debug(data[0])+" isInstanceof "+debug(data[1]));
    });

    Joose.A.each(negativeValues, function (data) {
      assertFalse(Psc.Code.isInstanceOf(data[0], data[1]), debug(data[0])+" isInstanceof "+debug(data[1]));
    });

    Joose.A.each(failureValues, function (data) {
      raises(function () {
        Psc.Code.isInstanceOf(data[0], data[1]);
      });
    });
  });
});