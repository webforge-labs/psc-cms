use(['Psc.Exception'], function () {
  module("Psc.Exception");
  
  //asyncTests damit das "use" class loading geht
  
  asyncTest("construct", function() {
    var e = new Psc.Exception('This will be shown');
    
    assertEquals('Psc.Exception',e.getName());
    assertEquals('This will be shown',e.getMessage());
    
    ok(e instanceof Psc.Exception);
    start();
  });
  
  // diese selftest ist für die assertions von unseren tests selbst und hat mit der library-logik eigentlich nichts zu tun
  // ich brauchte das hier zu demo zwekcen
  
  //test("selftest", function() {
  //  var e = new Psc.InvalidArgumentException('one',false);
  //  
  //  assertInstanceOf(Psc.Exception, e);
  //  assertInstanceOf(Psc.AjaxHandler, e);
  //
  //  var o = {};
  //  assertSame(o,o);
  //  assertSame(o,{});
  //  assertSame(o,o,"my objects are equal");
  //  assertSame(o,{},"my objects are equal");
  //  
  //  assertEquals("yes","no");
  //  assertEquals("yes","yes");
  //  assertEquals("yes","no", 'getter value ist richtig');
  //  assertEquals("yes","yes", 'getter value ist richtig');
  //  
  //  assertTrue(true, "bla bla gibt true zurück");
  //  assertTrue(false, "bla bla gibt true zurück");
  //
  //  fail('test ist blöd');
  //});
  
  asyncTest("selftestAssertException", function() {
    var thrown = function () {
      throw new (Class({
        isa: 'Psc.Exception'
      }))('Our nice Message');
    };
    
    var notThrown = function () {
      var t = 1+3;
    }
    
    assertException(null, thrown, 'Our nice Message');
    //assertException(null, notThrown, 'Our nice Message');
    start();
  });
});