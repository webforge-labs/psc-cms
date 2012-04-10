use(["Psc.InvalidArgumentException"], function () {
  module("Psc.InvalidArgumentException");

  test("construct", function() {
    var e = new Psc.InvalidArgumentException('method','POST|GET|DELETE|PUT');
  
    assertEquals("Psc.InvalidArgumentException", e.getName());
    assertEquals("method", e.getArg());
    assertEquals("POST|GET|DELETE|PUT", e.getExpected());
    assertEquals("[Psc.Exception 'Psc.InvalidArgumentException' with Message 'Falscher Parameter für Argument: 'method'. Erwartet wird: POST|GET|DELETE|PUT']", e.toString());
  });
  
  test("backtrace", function () {
    var e = new Psc.InvalidArgumentException('method','POST|GET|DELETE|PUT');
  });
});