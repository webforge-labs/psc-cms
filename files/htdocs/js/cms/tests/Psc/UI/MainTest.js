use(['Psc.UI.Main','Psc.UI.Tabs','Psc.UI.Tab','Psc.EventManager','Psc.EventManagerMock','Psc.Response', 'Psc.ResponseMetaReader'], function() {
  var main;
  var tabs;
  
  module("Psc.UI.Main", {
    setup: function () {
      tabs = new Psc.UI.Tabs({ widget: fixtures.loadHTML('ui-tabs') });
      main = new Psc.UI.Main({tabs: tabs});
    }
  });
  
  test("constructInitsEventManager", function() {
    assertInstanceOf(Psc.EventManager, main.getEventManager());
  });

  test("constructInitsFormHandler", function() {
    assertInstanceOf(Psc.AjaxFormHandler, main.getAjaxFormHandler());
  });

  test("constructCanBeInjectedWithEventManager", function() {
    var manager = new Psc.EventManager();
    var main = new Psc.UI.Main({eventManager: manager, tabs: tabs});
    
    assertInstanceOf(Psc.EventManager, main.getEventManager());
    assertSame(manager, main.getEventManager(), 'zurückgegebener manager ist der, der im Constructor übergeben wurde');
  });

  test("constructCanBeInjectedWithAjaxFormHandler", function() {
    var handler = new Psc.AjaxFormHandler();
    var main = new Psc.UI.Main({ajaxFormHandler: handler, tabs: tabs});
    
    assertInstanceOf(Psc.AjaxFormHandler, main.getAjaxFormHandler());
    assertSame(handler, main.getAjaxFormHandler(), 'zurückgegebener formHandler ist der, der im Constructor übergeben wurde');
  });
  
  test("constructNeedsTabsAsUIObject", function() {
    raises(function() {
      new Psc.UI.Main({tabs: null});
    });

    raises(function() {
      new Psc.UI.Main({tabs: $()});
    });
  });

  var responseMetaMock = Class({
    isa: Psc.ResponseMetaReader,
      
    has: {
      response: { is : 'r', required: false, isPrivate: true },
      data: { is : 'rw', required: true, isPrivate: false }
    }
  });

  test("event-form-saved triggers tab-open from meta", function() {
    var managerMock = new Psc.EventManagerMock({
      allow: 'form-saved',
      denySilent: true
    });
    
    var main = new Psc.UI.Main({eventManager: managerMock, tabs: tabs});
    var $form = $('<form></form>');
    var tabHook = undefined;
    // wir injecten hier direkt die meta-daten die sonst aus der Response gelesen würden
    var meta = new responseMetaMock({ data: {
      data: {
        tab: {
          id: 'new-tab-id',
          label: 'neuer Tab',
          url: '/not.avaible.html'
        }
      }
    }});
    
    main.attachHandlers(); // alle custom events werden gebindet, wir "filtern" aber durch den mock
    main.getEventManager().triggerEvent('form-saved', {}, [$form, meta, tabHook]);
    
    managerMock.wasTriggered('tab-open', 1, function(e, tab, $target) {
      assertInstanceOf(Psc.UI.Tab, tab);
      assertEquals('new-tab-id', tab.getId());
      assertEquals('neuer Tab', tab.getLabel());
      assertEquals('/not.avaible.html', tab.getUrl());
      
      assertEquals($form, $target, 'form wird als target übergeben');
    });
  });
  
  test("event-form-saved tabhook prevents tab-open", function() {
    var managerMock = new Psc.EventManagerMock({
      allow: 'form-saved',
      denySilent: true
    });
    
    var main = new Psc.UI.Main({eventManager: managerMock, tabs: tabs});
    var $form = $('<form></form>');
    var tabHook = function () { return false; };
    // wir injecten hier direkt die meta-daten die sonst aus der Response gelesen würden
    var meta = new responseMetaMock({ data: {
      data: {
        tab: {
          id: 'new-tab-id',
          label: 'neuer Tab',
          url: '/not.avaible.html'
        }
      }
    }});
    
    main.attachHandlers(); // alle custom events werden gebindet, wir "filtern" aber durch den mock
    main.getEventManager().triggerEvent('form-saved', {}, [$form, meta, tabHook]);
    
    assertTrue(managerMock.wasTriggered('tab-open', 0),'tab-open was not triggered allthough tabHook returns false');
  });

  test("event-form-saved triggers tab-close from meta", function() {
    var managerMock = new Psc.EventManagerMock({
      allow: 'form-saved',
      denySilent: true
    });
    
    var main = new Psc.UI.Main({eventManager: managerMock, tabs: tabs});
    var $form = $('<form></form>').appendTo(tabs.unwrap().find('#tabs-3'));
    var tabHook = undefined;
    // wir injecten hier direkt die meta-daten die sonst aus der Response gelesen würden
    var meta = new responseMetaMock({ data: {
      data: {
        tab: {
          close: true
        }
      }
    }});
    
    main.attachHandlers(); // alle custom events werden gebindet, wir "filtern" aber durch den mock
    main.getEventManager().triggerEvent('form-saved', {}, [$form, meta, tabHook]);
    assertTrue(managerMock.wasTriggered('tab-close', 1),'tab-close was triggered through meta');
  });
  
  test("when generalEvents are triggered from a Tab main transforms the $tab to the right tab", function() {
    var managerMock = new Psc.EventManagerMock({
      allow: 'unsaved', // nur so als beispiel, da die alle denselben handler haben
      denySilent: true
    });
    
    var $main = new Psc.UI.Main({eventManager: managerMock, tabs: tabs});
    main.attachHandlers();
    var $tab3 = tabs.unwrap().find('#tabs-3');
    $('<form><div class="somemarkup"><input class="guid1" type="text" /></div></form>').appendTo($tab3);
    var $input = $tab3.find('input.guid1');
    $input.trigger('unsaved');
    
    var eventTab, $eventTarget;
    managerMock.wasTriggered('tab-unsaved', 1, function (e, tab, $target) {
      eventTab = tab;
      $eventTarget = $target;
    });
    
    // the tab from which unsaved was fired
    assertSame($input[0], $eventTarget[0], 'target aus dem tab-unsaved event ist dasselbe wie das target von unsaved');
    assertEquals('tabs-3',eventTab.getId(), 'eventTab ist der richtige');
  });
});