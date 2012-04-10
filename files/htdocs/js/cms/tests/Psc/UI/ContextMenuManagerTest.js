use(['Psc.UI.ContextMenuManager','Psc.UI.Menu'], function() {
  module("Psc.UI.ContextMenuManager");

  test("acceptance", function() {
    var manager = new Psc.UI.ContextMenuManager({ });
  
    var $anchor = $('<span class="ui-icon-gear"></span>');
    $anchor.appendTo($('#qunit-fixture'));
    
    var menuOpen = false;
    var menuMockClass = Class({
      isa: 'Psc.UI.Menu',
      
      after: {
        open: function() {
          menuOpen = true;
        },
        close: function() {
          menuOpen = false
        }
      }
    });
    var menu = new menuMockClass({
      items: {
        'pinn': 'Permanent Anpinnen',
        'close-all': 'Alle Tabs Schließen',
        'save': 'Speichern'
      }
    });

    manager.register($anchor, menu);
    assertSame(menu, manager.get($anchor));
    
    raises(function () {
      manager.get($('body'));
    });
    
    $anchor.on('click', function (e) {
      e.preventDefault();
      manager.toggle($anchor);
    });
    
    assertFalse(menuOpen);
    $anchor.trigger('click');
    
    assertTrue(menu.unwrap().parents('body').length >= 1,'menu is appended somehow somewhere');
    assertTrue(menuOpen,'menu is opened through toggle');
    
    $anchor.trigger('click');
    assertFalse(menuOpen,'menu is closed through toggle');
  });
});