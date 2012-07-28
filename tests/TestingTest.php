<?php

namespace Psc;

/**
 * @group experiment
 */
class TestingTest extends \Psc\Code\Test\Base {
  
  protected $wrapperClosure;
  
  public function testJSONWithoutQuotes() {
    throw new Exception('fail');
      json_decode('{"columns":[{"name":"sound-number","label":"Sound No.","type":"string","index":0},{"name":"sound-content","label":"Sound","type":"string","index":1},{"name":"oids","label":"Correct OIDs","type":"array","index":2},{"name":"explanationSound-number","label":"ExplanationSound No.","type":"string","index":3},{"name":"explanationSound-content","label":"ExplanationSound","type":"string","index":4}],"data":[["2-TEST_1004","Find ein Karnickel",[[9999002,1]],[[9999001,1]],"2-TEST_2004","Richtig, da war es"],["2-TEST_1002","Finde den Affen",[[9999001,1]],"2-TEST_2002","Hoots hoots hoots"],["2-TEST_1003","Finde die Giraffe",[[9999003,1]],"2-TEST_2003","Giraffen machen wenig schöne Sounds"]]}');

  }
  
  public function testJSONConvertFromJS() {
    $json = json_decode('{"table":[[null,"Finde die Fußspur des g...le Abdrücke im Bild an.","GAME-4-002"],[null,"Das war nicht richtig!","GAME-4-011"],[null,"Sieh dir noch einmal in...inmal spielen möchtest.","GAME-4-012"],[null,"Probiere es noch einmal","GAME-4-013"],[null,"Bitte berühre einen Fussabdruck","GAME-4-014"]]}');
    
  }
  
  public function testObjectCount() {
    $o = new \stdClass;


    $this->assertEquals(0, count(get_object_vars($o)));
  }
  
  public function testClosureScoping() {
    
    $data = array();
    
    $modifying = function () use (&$data) {
      $data[] = TRUE;
    };


    $wrapper = function () use ($modifying) {
      $modifying();
    };
    
    $this->wrapperClosures = array('f'=>$wrapper);
    
    $this->callWrapperClosure();
    $this->assertEquals(array(0=>TRUE), $data);
  }
  
  protected function callWrapperClosure() {
    $this->wrapperClosures['f']();
  }
  
  public function testNumberFormat() {
    $this->assertEquals('1292,25', number_format(1292.25, 2, ',', ''));
  }
}

class ToStringObject {
  
  
  public function __toString() {
    return 'yes im running';
  }
}
?>