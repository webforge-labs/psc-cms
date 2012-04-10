use(['Psc.UI.Tab'], function() {
  module("Psc.UI.Tab");

  test("construct", function() {
    var tab = new Psc.UI.Tab({
      id: 'entity-persons-17',
      label: 'Philipp S',
      url: '/entities/persons/17/form'
    });
    
    assertEquals('entity-persons-17', tab.getId());
    assertEquals(null, tab.getContent());
    assertEquals('Philipp S', tab.getLabel());
    assertEquals('/entities/persons/17/form', tab.getUrl());
  });
});