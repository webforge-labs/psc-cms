use(['Psc.Response'], function () {
  module("Psc.Response", {
    setup: function () {
      this.headerString = "Date: Mon, 19 Mar 2012 06:48:32 GMT\r\nServer: Apache/2.2.22 (Win32) PHP/5.3.10\r\nX-Powered-By: PHP/5.3.10\r\nPragma: no-cache\r\nCache-Control: private, no-cache\r\nVary: Accept\r\nContent-Length: 50\r\nKeep-Alive: timeout=5, max=91\r\nConnection: Keep-Alive\r\nContent-Type: text/html; charset=utf-8\r";
    }
  });
  
  test("construct", function() {
    
    var response = new Psc.Response({code: 200, reason: 'OK'});
    
    assertEquals(200, response.getCode());
    assertEquals('OK', response.getReason());
    assertEmptyObject(response.getHeader());
    
    assertInstanceOf(Psc.Response, response);
  });
  
  test("convertsCodeToInt", function() {
    var response = new Psc.Response({code: "200", reason: 'OK'});
    
    assertEquals(200, response.getCode());
  });
  
  test("parsesStringHeader", function() {
    var response = new Psc.Response({
      code: 200,
      reason: 'OK',
      headers: this.headerString
    });
    
    
    assertEquals("text/html; charset=utf-8", response.getHeaderField('Content-Type'));
    assertEquals("no-cache", response.getHeaderField('Pragma'));
    
    // sets to null
    response.setHeaderField('Content-Type', null);
    assertEquals(null, response.getHeaderField('Content-Type'));
    
    // removes
    response.removeHeaderField('Pragma');
    assertEquals(null, response.getHeaderField('Pragma'));
  });
});