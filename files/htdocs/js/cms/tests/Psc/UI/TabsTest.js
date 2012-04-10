use(['Psc.UI.Tabs','Psc.UI.Tab','Psc.UI.Menu'], function() {
  var tab = new Psc.UI.Tab({
    id: 'entity-persons-17',
    label: 'Philipp S',
    url: '/entities/persons/17/form'
  });
  var otherTab = new Psc.UI.Tab({
    id: 'entity-persons-19',
    label: 'IS',
    url: '/entities/persons/19/form'
  });
  
  var loadFixture = function(assertions) {
    return function () {
      $.get('/js/fixtures/tabs.php', function (html) {
        var $fixture = $('#qunit-fixture').html(html);
        var $tabs = $fixture.find('div.psc-cms-ui-tabs');
        assertEquals($tabs.length,1,'self-test: Fixture hat div.psc-cms-ui-tabs im html des Ajax Requests');
      
        var tabs = new Psc.UI.Tabs({ widget: $tabs });
        assertSame($tabs, tabs.unwrap());
        
        assertions($tabs, tabs);
      }, 'html');
    };
  };
  
  asyncTest("tab parsing", loadFixture(function ($tabs, tabs) {
    var welcome;
    // check welcome tab (parsing)
    assertInstanceOf(Psc.UI.Tab, welcome = tabs.tab({index: 0}));
    assertEquals('Willkommen', welcome.getLabel());
    assertEquals('tab-content0', welcome.getId());
    assertFalse(welcome.isClosable(),'welcome cannot be closed');
    
    assertEquals(0, tabs.getIndex());
    assertEquals(1, tabs.count());
    start();
  }));
    
  asyncTest("has", loadFixture(function ($tabs, tabs) {
    // has
    assertFalse(tabs.has(),'has with empty');
    assertFalse(tabs.has({index: null}),'has() returns false with null as index');
    assertFalse(tabs.has({index: 2}),'has() returns false with not-existing index');
    assertTrue(tabs.has({index: 0}),'has() returns true with existing index');
    start();
  }));
  
  asyncTest("add a tab and get his index", loadFixture(function ($tabs, tabs) {
    tabs.add(tab);
    assertTrue(tab.isClosable(),'normal tab is closable');    
    
    // index + count
    assertEquals(1, tabs.getIndex());
    assertEquals(2, tabs.count());
    assertEquals(1, tabs.getIndex(tab));
  
    // now it has the tab
    assertTrue(tabs.has(tab));
    
    // but not another
    assertFalse(tabs.has(otherTab));
    start();
  }));
  
  
  asyncTest("close and isClosable", loadFixture(function ($tabs, tabs) {
    tabs.add(tab);
    assertTrue(tab.isClosable(),'normal tab is closable');
    
    assertFalse(tabs.tab({index:0}).isClosable(), 'welcome tab is not closable');
    start();
  }));
  
  asyncTest("search for a tab", loadFixture(function ($tabs, tabs) {
    tabs.add(tab);
    
    // search
    assertSame(tab, tabs.tab({id: 'entity-persons-17'}));
    assertSame(tab, tabs.tab({index: tabs.getIndex()}));
    
    raises(function () {
      tabs.tab({id: 'none'});
    });
    raises(function () {
      tabs.tab({index: 2});
    });
    raises(function () {
      tabs.tab({index: -1});
    });
    start();
  }));
    
    
  asyncTest("select a tab and open adds and selects tabs", loadFixture(function ($tabs, tabs) {
    tabs.add(tab);
    var selected = function() {
      return $tabs.tabs('option','selected');
    };
    
    // select
    assertEquals(0, selected(), 'pre-condition: nativer index entspricht nicht dem erwarteten');
    tabs.select(tab);
    // native test
    assertEquals(1, selected(), 'nativer index entspricht nicht dem erwarteten');
    
    // "open" a new tab. the first one just adds it
    tabs.open(otherTab);
    assertEquals(2, tabs.getIndex(otherTab));
    
    // tab is not selected
    assertEquals(1, selected(), 'nativer index ist noch 1');
    // tab is added
    assertEquals(3, tabs.count()); // tab,othertab,welcome
    assertTrue(tabs.has(otherTab),'has() gibt für den open tab true zurück');
    // native test isSelected?
    assertEquals(1, $tabs.find('ul li a[href="#entity-persons-19"]').length,' tab wurde geöffnet und hinzugefügt (dom)');
    
    //  "open" the second selects it
    tabs.open(otherTab);
    assertEquals(2, selected(),'tab was selected the second time');
    start();
  }));
  
  asyncTest("mark tabs unsaved and saved. ", loadFixture(function ($tabs, tabs) {
    tabs.add(tab);
    tabs.add(otherTab);
    
    // mark unsaved and then saved
    assertFalse(tab.isUnsaved());
    tabs.unsaved(tab);
    assertTrue(tab.isUnsaved());
    tabs.saved(tab);
    assertFalse(tab.isUnsaved());
    
    // close from code (tab)
    tabs.close(tab);
    assertEquals(2, tabs.count());
    
    // close from click (otherTab), otherTab wurde ganz sicher hinzugefügt, d.h. die eventhandler klappen auch für sich veränderne elemente
    $tabs.find('ul li a[href="#entity-persons-19"]').nextAll('span.ui-icon-close').trigger('click');
    assertEquals(1, tabs.count(),'count ist jetzt 1 nachdem auf close gelickt wurde');
    start();
  }));
  
  asyncTest("tabsAll closes (nearly) all tabs", loadFixture(function ($tabs, tabs) {
    start();
    tabs.add(tab);
    tabs.add(otherTab);
    assertEquals(3, tabs.count(),'3 tabs are there (1 fixture + 2 real)'); // 3 denn auch welcome
    
    tabs.closeAll();
    assertEquals(1, tabs.count(),'1 tabs is avaible after closeAll() because it is not closable');
    assertFalse(tabs.tab({index:0}).isClosable(), 'tab is really not closable');
  }));
  
  asyncTest("tabs gets added with contextMenu", loadFixture(function ($tabs, tabs) {
    tabs.add(tab);
    start();
    
    assertEquals(2, tabs.count(),'tabs count is 2');
    var $li = $tabs.find('li:has(a[href="#entity-persons-17"])');
    var $span = $li.find('span.ui-icon-gear');
    
    // is registered in manager
    var menu = tabs.getContextMenuManager().get($span);
    
    assertInstanceOf(Psc.UI.Menu, menu);
  }));
  
  asyncTest("tabs gets parsed with contextMenu", loadFixture(function ($tabs, tabs) {
    start();
    fail('todo');
  }));
});