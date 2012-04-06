<?php

class AggregationObjectTest extends \Psc\Code\Test\Base {
  
  
  public function testAggregation_NonSimpleInnerObject() {
    $this->markTestSkipped('noch nicht running - ist auch schwierig ich glaub es geht hier nur MIT gettern
                           objekte mit public properties sind echt hart hier zu relaisieren
                           (und konstistent mit der simpleobject klasse erst recht)');
    $inner = new InnerNonSimpleObject();
    $this->runAggregationTest($inner);
  }
  
  public function testAggregation_SimpleInnerObject() {
    $inner = new InnerObject();
    $this->runAggregationTest($inner);
  }
  
  protected function runAggregationTest($inner) {
    $o = new OuterObject($inner);
 
// die gehen alle nicht, weil php und so (beim compilen wäre das geiler)
//    $this->assertObjectHasAttribute('prop1', $o);
//    $this->assertObjectHasAttribute('prop2', $o);
//    $this->assertObjectHasAttribute('prop3', $o);
    
//    $this->assertTrue($o->hasMethod('modifyProp2'),'Outer Object hat nicht modifyProp2');
//    $this->assertTrue($o->hasMethod('getProp1'),'Outer Object hat nicht getProp1');
//    $this->assertTrue($o->hasMethod('getProp2'),'Outer Object hat nicht getProp2');
//    $this->assertTrue($o->hasMethod('getProp3'),'Outer Object hat nicht getProp3');

//    $this->assertTrue($o->hasMethod('setProp1'),'Outer Object hat nicht setProp1');
//    $this->assertTrue($o->hasMethod('setProp2'),'Outer Object hat nicht setProp2');
//    $this->assertTrue($o->hasMethod('setProp3'),'Outer Object hat nicht setProp3');

    $this->assertEquals('outerObjectValue',$o->getProp4());
    $this->assertEquals('innerObjectValue',$o->getProp5());
    
    $this->assertEquals('prop1value',$o->getProp1());
    $this->assertEquals(array('bananen'),$o->getProp2());
    $this->assertEquals('schnippi',$o->getProp3());
    
    $o->modifyProp2();
    
    $this->assertEquals(array('bambelu','bananen'),$o->getProp2());
    
    $o->setProp3('nix');
    $this->assertEquals('nix',$o->getProp3());
  }
}

class InnerObject extends \Psc\Object {
  
  protected $prop1 = 'prop1value';
  protected $prop2 = array('bananen');
  protected $prop3 = NULL;
  protected $prop4 = 'innerObjectValue';
  
  protected $prop5 = 'innerObjectValue';
  
  public function __construct() {
    $this->prop3 = 'schnippi';
  }
  
  public function modifyProp2() {
    array_unshift($this->prop2,'bambelu');
    return $this;
  }
  
}

class InnerNonSimpleObject {
  
  protected $prop1 = 'prop1value';
  protected $prop2 = array('bananen');
  public $prop3 = NULL;
  public $prop4 = 'innerObjectValue';
  
  public $prop5 = 'innerObjectValue';
  
  public function __construct() {
    $this->prop3 = 'schnippi';
  }
  
  public function modifyProp2() {
    array_unshift($this->prop2,'bambelu');
    return $this;
  }
  
  public function getProp2() {
    return $this->prop2;
  }

  public function getProp1() {
    return $this->prop1;
  }
  
  public function getProp5() {
    return 'innerObjectValue';
  }
}

class OuterObject extends \Psc\AggregationObject {
  
  protected $object;
  
  protected $prop5 = 'outerObjectValue'; // wird nicht genommen weil es in outer keinen Getter für Prop5 gibt

  public function __construct($inner) {
    $this->object = $inner;
    parent::__construct('object');
  }
  
  public function getProp4() {
    return 'outerObjectValue';
  }
}

?>