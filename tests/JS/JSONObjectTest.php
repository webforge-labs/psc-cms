<?php

namespace Psc\JS;

use \Psc\JS\JSONObject;

class TestObject extends JSONObject {
  
  protected $title = 'titleval';
  
  protected $items = array(
    'item1',
    'item2',
    'item3'
  );
  
  protected $nestedItems = array();
  
  public function __construct($nested = FALSE) {
    if ($nested) {
      $this->nestedItems[] = new TestObject();
      $this->nestedItems[] = new TestObject();
    }
  }
  
  
  protected function getJSONFields() {
    return array('title'=>NULL,
                 'items'=>NULL,
                 'nestedItems'=>'JSON[]'
                );
  }
}

class JSONObjectTest extends \Psc\Code\Test\Base {

  public function testExport() {
    $o = new TestObject(TRUE);
    
    $ex = new \stdClass;
    $ex->title = 'titleval';
    $ex->items = array();
    $ex->items[] = 'item1';
    $ex->items[] = 'item2';
    $ex->items[] = 'item3';

    $ne = new \stdClass;
    $ne->title = 'titleval';
    $ne->items = array();
    $ne->items[] = 'item1';
    $ne->items[] = 'item2';
    $ne->items[] = 'item3';
    $ne->nestedItems = array();
    
    $ex->nestedItems = array();
    $ex->nestedItems[] = $ne;
    $ex->nestedItems[] = $ne;
    
    $this->assertEquals($ex, $o->exportJSON());
  }
}
?>