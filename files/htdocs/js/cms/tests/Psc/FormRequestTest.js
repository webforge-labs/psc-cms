use(['Psc.FormRequest'], function () {
  module("Psc.FormRequest");
  
  asyncTest("parsesURLAndMethodFromCMSForm", function() {
    $.get('/js/fixtures/form.html', function (html) {
      var $form = $(html);
      var formRequest = new Psc.FormRequest({ form: $form});
      
      assertEquals('/js/fixtures/ajax/http.form.saved.php',formRequest.getUrl());
      assertEquals('PUT',formRequest.getMethod());
    
      start();
    });
  });
});