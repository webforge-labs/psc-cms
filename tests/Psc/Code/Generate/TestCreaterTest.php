<?php

namespace Psc\Code\Generate;

use Psc\Code\Generate\TestCreater;

/**
 * @group generate
 * @group class:Psc\Code\Generate\TestCreater
 */
class TestCreaterTest extends \Psc\Code\Test\Base {

  public function testStub() {
    $gClass = new GClass('Psc\Code\Generate\MyTestClass');
    $out = $this->newFile('MyTestClassTest.php');
    
    $out->delete();
    
    $tc = $this->getMock('Psc\Code\Generate\TestCreater', array('getTestFile'), array($gClass));
    $tc->expects($this->once())
       ->method('getTestFile')
       ->will($this->returnValue($out));
    
    $outTc = $tc->create();
    $this->assertSame($out, $outTc); // checkt ob unser mock geklappt hat
    
    require $out;
    
    $this->assertTrue(class_exists('Psc\Code\Generate\MyTestClassTest', FALSE));
    $test = GClass::factory('Psc\Code\Generate\MyTestClassTest');
    
    $db = $test->getDocBlock();
    $this->assertContains('@group class:Psc\Code\Generate\MyTestClass', $db->toString());
    $this->assertTrue($test->hasMethod('setUp'),'methode setUp existiert nicht im Test');
    $this->assertTrue($test->hasMethod('testAcceptance'),'methode testAcceptance existiert nicht im Test');

    $this->assertEquals('Psc\Code\Test\Base', $test->getParentClass()->getFQN());// achtung das hier ist kein instanceof
  }
}
?>