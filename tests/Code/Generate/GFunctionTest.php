<?php

namespace Psc\Code\Generate;

use \Psc\Code\Generate\GFunction;

/**
 * @group generate
 * @group class:Psc\Code\Generate\GFunction
 */
class GFunctionTest extends \Psc\Code\Test\Base {

  public function testBody() {
    /* Gleich 2 Bugs:
        1. Body war leer
        2. wenn Klammer im Body, wurden diese rausgeschnitten
    */
    
    $function = new GFunction ('testing', array(),
<<< 'FUNCTION_BODY'
if (isset($this->body)) {
  throw new Exception('nicht gesetzt');
}
FUNCTION_BODY
    );
    
    $expectedFunction =<<< 'EXPECTED_FUNCTION'
function testing() {
  if (isset($this->body)) {
    throw new Exception('nicht gesetzt');
  }
}
EXPECTED_FUNCTION;

    //file_put_contents('D:\fixture.txt', $expectedFunction);
    //file_put_contents('D:\compiled.txt',$function->php());
    $this->assertEquals($expectedFunction, $function->php());
  }
  
  /**
   * @expectedException Psc\Code\Generate\SyntaxErrorException
   */
  public function testParseError() {
    $function = new GFunction('erroneous', array());
    $function->setSourceCode(" \$das ist natürlich quatsch ");
    /* okay das ist schon gemein hacky
      normalerweise muss die reflection ja die methode / funktion laden könne
      und das kann die ja auch nicht, wenn sie einen syntax error hat
      aber so testen wir wenigsten noch, ob wir intern was vergimbeln
      
      spätestens aber beim ClassWriter würden wir was bemerken
    */
    
    $function->php(); // throws
  }
}
?>