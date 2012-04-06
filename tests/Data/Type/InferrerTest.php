<?php

namespace Psc\Data\Type;

use Psc\Data\Type\Inferrer;
use Psc\Code\Generate\GClass;

class InferrerTest extends \Psc\Code\Test\Base {

  /**
   * @dataProvider provideTestInferType
   */
  public function testInferType($value, $expectedType) {
    $inferrer = new Inferrer();
    
    $type = $inferrer->inferType($value);
    $this->assertEquals($expectedType, $type);
  }
  
  
  public static function provideTestInferType() {
    $tests = array();
    
    $tests[] = array('ichbineinstring', new StringType);
    $tests[] = array(12, new IntegerType);
    $tests[] = array(true, new BooleanType);
    $tests[] = array(array('eins','zwei',12,'schwierig'), new ArrayType);
    $tests[] = array(new \stdClass, new ObjectType(new GClass('stdClass')));
    
    return $tests;
  }
  
  /**
   * @expectedException Psc\Data\Type\InferException
   */
  public function testInferTypeIsNotComplete() {
    $this->testInferType(12.34, 'none');
  }
}
?>