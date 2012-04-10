use(['Psc.AjaxHandler', 'Psc.Request'], function () {
  module("Psc.AjaxHandler", {
    setup: function () {
      this.ajaxHandler = new Psc.AjaxHandler();
      this.request404 = new Psc.Request({
       url: '/js/fixtures/ajax/http.404.php',
        method: 'PUT'
      });
      
      this.request200 = new Psc.Request({
        url: '/js/fixtures/ajax/http.echo.php',
        body: {
          content: 'delivered body',
          headers: { 'X-Test-Header': '1'}
        },
        method: 'POST',
        format: 'html'
      });
      
      this.request400Validation = new Psc.Request({
        url: '/js/fixtures/ajax/http.echo.php',
        body: {
          content: '400 validation body',
          code: 400,
          headers: { 'X-Psc-Cms-Validation': 'true' }
        },
        method: 'POST',
        format: 'html'
      });
    }
  });
  
  asyncTest("HandlerCallsRejectOn404Error", function() {
    var req = this.ajaxHandler.handle(this.request404);
    
    req.fail(function(response) {
      assertInstanceOf(Psc.Response, response);
      assertEquals(404, response.getCode());
      assertEquals("Dies ist ein 404 TestFehler (Seite nicht gefunden)", response.getBody());
      
      start();
    });
    
    req.done(function() {
      fail('done is called');
      start();
    });
  });
  
  asyncTest("400ValidationRejectsPromise_isValidation", function() {
    var req = this.ajaxHandler.handle(this.request400Validation);
    
    req.fail(function(response) {
      assertInstanceOf(Psc.Response, response);
      assertEquals(400, response.getCode());
      assertEquals("400 validation body", response.getBody());
      assertTrue(response.isValidation());
      
      start();
    });
    
    req.done(function(response) {
      fail('handler calls done instead of fail');
      
      start();
    });
  });
  
  asyncTest("200hasCodehasBodyhasHeader", function() {
    var req = this.ajaxHandler.handle(this.request200);
    
    req.done(function(response) {
      assertInstanceOf(Psc.Response, response);
      assertEquals(200, response.getCode());
      assertEquals("delivered body", response.getBody());
      assertFalse(response.isValidation());
      assertEquals('1', response.getHeaderField('X-Test-Header'),'X-Test-Header is equal to 1');
      
      start();
    });
  });
  
  //test("handleParserError", function() {
  //  var ajaxHandler = new Psc.AjaxHandler();
  //  
  //  raises(function () {
  //  
  //  var req = ajaxHandler.handle(new Psc.Request({
  //      url: '/js/fixtures/ajax/http.echo.php',
  //      body: {
  //        headers: {
  //          'X-Psc-Cms-Validation': 'false',
  //          'Content-Type': 'text/html; charset="UTF-8"'
  //        },
  //        content: 'i am the echo'
  //      },
  //      method: 'PUT',
  //      format: 'json' // mismatch mit oben
  //  }));
  //  
  //  
  //    req.always(function () {
  //      console.log('walway');
  //    });
  //    req.done(function(response) {
  //      //fail('done darf nie ausgeführt werden, weil dies ein Interner fehler ist');
  //    });
  //  
  //  }); 
  //  
  //  //fail('Es wurde keine Psc.Exception bearbeitet');
  //});
});