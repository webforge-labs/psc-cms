use(['Psc.EventManager'], function() {
  var eventManager;
  
  module("Psc.EventManager", {
    setup: function() {
      eventManager = new Psc.EventManager({ });
    }
  });

  
  test("new Instance does not get triggered", function() {
    var manager1 = new Psc.EventManager();
    var manager2 = new Psc.EventManager();
    var called = false;
    
    //eventManager.on('*', function(e) {
      //e.stopImmediatePropagation();
    //});
    
    manager1.on('testEvent', function(e) {
      fail('manager1 on must not be called');
    });

    manager2.on('testEvent', function(e) {
      called = true;
    });
    
    manager2.triggerEvent('testEvent');
    assertTrue(called, 'manager2 Event is called');
  });

  test("construct", function() {
    expect(0);
  });
  
  test("nativeBinding", function () {
    eventManager.on('tsing', function (e) {
      assertEquals('tsing', e.type, 'got event');
    });
    
    var ev = jQuery.Event('tsing', {schnurps: 'blubb'});
    
    eventManager.trigger(ev);
  });
  
  test("constructsJQueryEvents", function() {
    var savedEvent = eventManager.createEvent('saved', { method: 'edit' }); // nicht type benutzen als property das braucht jQuery
    
    // erstmal hinten wir nur das hier
    assertEquals('edit', savedEvent.method);
    assertEquals('saved', savedEvent.type);
  });
  
  test("canBeTriggeredWithEvents_acceptsOnAndTrigger", function() {
    var eventCount = 0;
    eventManager.on('saved', function (e, add1, add2) { // meint Psc.saved
      assertEquals('add1', add1, 'additional Parameter wird im Handler übergeben');
      assertEquals('add2', add2);
      assertEquals('saved', e.type);
      assertEquals('edit', e.method);
      
      eventCount++;
    });
    
    eventManager.trigger(
      eventManager.createEvent('saved', { method: 'edit'}),
      ['add1','add2']
    );
    
    // or aequivalent
    eventManager.triggerEvent('saved', { method: 'edit'}, ['add1','add2']);
    
    assertEquals(2, eventCount, '2 Events were triggered');
  });
  
  test("stopsWithPreventedBefore", function() {
    var eventCount = 0;
    var eventManager = new Psc.EventManager();
    // wer zuerst kommt und e.stopImmediatePropagation drückt gewinnt
    
    eventManager.on('test', function (e) {
      eventCount++;
      e.stopImmediatePropagation();
    });

    eventManager.on('test', function (e) {
      eventCount++; // does never happen
      fail('second test handler is called');
    });
    
    eventManager.triggerEvent('test');
    assertEquals(1,eventCount,'only one event is called');
  });

  test("bindsWildcard", function() {
    var eventCount = 0;
    var eventManager = new Psc.EventManager();
    // wer zuerst kommt und e.stopImmediatePropagation drückt gewinnt
    
    eventManager.on('test test2 test3', function (e) {
      e.stopImmediatePropagation();
    });

    eventManager.on('test', function (e) {
      eventCount++; // does never happen
      fail('first test handler is called');
    });

    eventManager.on('test2', function (e) {
      eventCount++; // does never happen
      fail('second test handler is called');
    });

    eventManager.on('test3', function (e) {
      eventCount++; // does never happen
      fail('third test handler is called');
    });
    
    eventManager.triggerEvent('test');
    eventManager.triggerEvent('test2');
    eventManager.triggerEvent('test3');
    assertEquals(0,eventCount, 'no event is called');
  });
});