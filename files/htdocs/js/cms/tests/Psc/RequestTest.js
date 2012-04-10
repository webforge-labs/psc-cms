use(['Psc.Request'], function () {
  module("Psc.Request");
  
  test("construct", function() {
    var request = new Psc.Request({
      url: '/entities/person/12/form',
      method: 'PUT'
    });
    
    assertEquals('/entities/person/12/form', request.getUrl());
    
    assertException("Psc.WrongValueException", function () {
      request.setFormat('blubb')
    });
  
    assertException("Psc.WrongValueException", function () {
      request.setMethod('PUD')
    });
  });
  
  test("bodyConstruct", function () {
    var request = new Psc.Request({
      url: '/echo/',
      method: 'POST',
      format: 'html',
      body: 'not empty'
    });
    
    assertEquals('not empty',request.getBody());
    assertEquals(null, request.getHeaderField('X-Psc-Cms-Request-Method'));
    assertEquals('POST',request.getMethod());
  });
  
  test("headerIsEmptyAtStart", function () {
    var request = new Psc.Request({
      url: '/entities/person/12/form',
      method: 'POST'
    });
    
    assertEmptyObject(request.getHeader());
    assertEquals(null, request.getHeaderField('Content-Type'));
  });
  
  test("requestGetsandSetsHeaderFields", function() {
    var request = new Psc.Request({
      url: '/entities/person/12/form',
      method: 'POST'
    });
    
    assertEquals(null, request.getHeaderField('Content-Type'));
    request.setHeaderField('Content-Type', 'text/html');
    assertEquals('text/html', request.getHeaderField('Content-Type'));
    request.removeHeaderField('Content-Type');
    assertEquals(null, request.getHeaderField('Content-Type'));
  });
  
  test("setMethodSetsXRequestMethodHeader", function() {
    var request = new Psc.Request({
      url: '/entities/person/12/form',
      method: 'PUT'
    });
    var xHeader = 'X-Psc-Cms-Request-Method';
  
    // initialize sets it
    assertEquals('PUT', request.getHeaderField(xHeader));
  });
});