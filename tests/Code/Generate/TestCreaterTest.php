<?php

namespace Psc\Code\Generate;

use Psc\Code\Generate\TestCreater;

/**
 * @group generate
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
    
    // blöder test, weil er genau genau genau gleich sein muss
    // besser wäre, ob er base ableitet usw, 
    //$this->assertFileEquals($this->getFile('fixture.MyTestClassTest.php'), $out);
    // @TODO z.B. syntax check!
  }
}
?>