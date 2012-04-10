use(['Psc.UI.Menu'], function() {
  module("Psc.UI.Menu");

  test("acceptance", function() {
    var html = "<ul>\n"+
    "		<li><a href='#pinn'>Permanent Anpinnen</a></li>\n"+
    "		<li><a href='#close-all'>Alle Tabs Schließen</a></li>\n"+
    "		<li><a href='#save'>Speichern</a></li>\n"+
    "	</ul>\n";
    
    var menu = new Psc.UI.Menu({
      items: {
        'pinn': 'Permanent Anpinnen',
        'close-all': 'Alle Tabs Schließen',
        'save': 'Speichern'
      }
    });
    
    var $menu = menu.unwrap();
    
    assertEquals(1, $menu.find('li a[href="#pinn"]').length);
    assertEquals(1, $menu.find('li a[href="#close-all"]').length);
    assertEquals(1, $menu.find('li a[href="#save"]').length);
    
    assertTrue($menu.data('menu') !== undefined);
    
    assertFalse(menu.isOpen(),' !isOpen() by default');
    menu.open();
    assertTrue(menu.isOpen(),'isOpen() after open()');
    menu.close();
    assertFalse(menu.isOpen(),'!isOpen() after close()');
  });
});