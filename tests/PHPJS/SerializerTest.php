<?php

use Psc\Object,
    \Psc\PHPJS\Object AS PHPJSObject,
    \Psc\PHPJS\Serializer,
    \Psc\Code\Code
;

class ElviriaMenuItem extends Object implements PHPJSObject {
  
  public static $languages = array('en'=>'English','es'=>'Espanol');
  
  /**
   * @var string h1|h2|entry
   */
  protected $type;
  
  /**
   * @var int
   */
  protected $index;
  
  
  public function __construct($type) {
    $this->type = $type;
  }
  
  /**
   *
   * alle Felder müssen einen Getter haben
   */
  public function getJSFields() {
    return array('type',
                 'description',
                 'index'
                );
  }
  
  /**
   * @var array languageArray
   */
  protected $description;
  
  
  public static function factoryJSData(Array $data, $c) {
    $item = new $c($data['type']);
    $item->setDescription($data['description']);
    $item->setIndex($data['index']);
    return $item;
  }
}

class ElviriaHeadline extends ElviriaMenuItem {
  public function __construct() {
    $this->type = 'h1';
  }
  
  
}

class ElviriaSubheadline extends ElviriaMenuItem {
  public function __construct() {
   $this->type = 'h2';
  }
}
  
class ElviriaEntry extends ElviriaMenuItem {
  public function __construct() {
    $this->type = 'entry';
  }
}

class ElviriaMenu extends Object implements PHPJSObject {

  protected $items;
  
  public function __construct() {
    $this->items = func_get_args();
  }
  
  public function getJSFields() {
    return array('items');
  }
  
  public static function factoryJSData(Array $data, $c) {
    return Object::factory($c,$data['items'],Object::REPLACE_ARGS);
  }
}

class PHPJSSerializerTest extends PHPUnit_Framework_TestCase {
  
  public function testWholeShitElviria() {
    $headline = new ElviriaHeadline();
    $headline->setDescription(array('en'=>'Hauptgerichte','es'=>'Los Hauptgerichtos'));
    $headline->index = 1;

    $entry1 = new ElviriaEntry();
    $entry1->setDescription(array('en'=>'Fried Chips','es'=>'Los frittos hottos'));
    $entry1->index = 2;

    $entry2 = new ElviriaEntry();
    $entry2->setDescription(array('en'=>'Fried Krabbenballs','es'=>'Los frittos Crabbos (Ballos)'));
    $entry2->index = 3;

    $menu = new ElviriaMenu($headline,$entry1,$entry2);
    $data = $menu;

    $s = new Serializer();
    $this->assertEquals(array(), $s->getMetadata());
    
    $json = $s->serialize($data);
    $this->assertInternalType('string',$json);
    $this->assertNotEmpty($json,'Json String leer');
    
    
    $metaJson = $s->getMetadataJSON();
    $this->assertInternalType('string',$metaJson);
    $this->assertNotEmpty($metaJson,'meta Json String leer');
    $this->assertNotEquals('[]',$metaJson,'keine Metadaten für das Objekt gespeichert (Fehler beim serializieren)');
    
    /* zwischentest */
    $ex = false;
    try {
      $s = new Serializer(); // keine Metadaten
      
      $s->unserialize($json,'ElviriaMenu');
    } catch (\Psc\PHPJS\Exception $e) {
      $ex = TRUE;
    }
    $this->assertTrue($ex,'Keine Exception beim Aufruf ohne Metadaten mit richtigem Objekt');

    $s = new Serializer($metaJson);
    $this->assertNotEmpty($s->getMetadata());
    $sdata = $s->unserialize($json,'ElviriaMenu');
    
    $this->assertEquals($data, $sdata, 'Der gesamte Test ging schief, weil die objekte unterschiedlich sind');
  }
}

?>