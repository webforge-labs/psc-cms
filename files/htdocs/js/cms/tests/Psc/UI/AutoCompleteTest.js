use(['Psc.UI.AutoComplete','Psc.EventManagerMock','Psc.AjaxHandler','Psc.UI.Tab'], function() {
  var html, $autoComplete;
  
  module("Psc.UI.AutoComplete", {
    setup: function () {
      html =
      '<div class="input-set-wrapper"><input type="text" style="width: 90%" id="identifier" class="text ui-widget-content ui-corner-all autocomplete sc-guid-identifier" value="" name="identifier">'+
      ''+
      '<br><small class="hint">Sound-Suche nach Volltext, Soundnummer oder #tiptoi-cms-id</small>'+
      '</div>';
    
      var $html = $(html);
      $('#qunit-fixture').append($html);
      //$('body').append($html);
      $autoComplete = $html.find('input[name="identifier"]');
    }
  });
  
  test("acceptance", function() {
    var ajaxHandlerMockClass = Class({
      isa: Psc.AjaxHandler,
      
      after: {
        handle: function (request) {
          assertEquals('gira', request.getBody().search, 'gira is send as request term');
        }
      }
    });
    
    var evm;
    var autoComplete = new Psc.UI.AutoComplete({
      ajaxHandler: new ajaxHandlerMockClass(),
      eventManager: evm = new Psc.EventManagerMock({denySilent:true, allow: []}),
      delay: 100,
      minLength: 1,
      url: '/js/fixtures/ajax/http.autocomplete.response.php',
      widget: $autoComplete
    });
    
    assertEquals(100, $autoComplete.autocomplete('option','delay'),'delay is put to widget');
    assertEquals(1, $autoComplete.autocomplete('option','minLength'), 'minlength is put to widget');
    
    // jetzt faken wir das "tippen" ins feld
    $autoComplete.simulate( "focus" )['val']( "gira" ).keydown();
    stop();

    // die jquery-ui-guys machen das hier so
    setTimeout(function() {
      start();
	  assertTrue( $autoComplete.is( ":visible" ), "menu is visible after delay" );
      // select first
	  $autoComplete.simulate( "keydown", { keyCode: $.ui.keyCode.DOWN } );
	  $autoComplete.simulate( "keydown", { keyCode: $.ui.keyCode.ENTER } );
      // blur must be async for IE to handle it properly
	  setTimeout(function() {
        $autoComplete.simulate( "blur" );
      }, 1 );
      
      assertNotFalse(evm.wasTriggered('tab-open', 1, function (e, tab, $target) {
        assertSame($target[0], $autoComplete[0],'target from event is the autocomplete');
        assertInstanceOf(Psc.UI.Tab, tab, 'event Parameter eins ist ein Psc.UI.Tab');
        return true;
      }), 'tab-open was triggered');
    }, 300 ); // bigger than delay
  });
});