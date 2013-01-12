<?php

namespace Psc\Code\Test\Mock;

/**
 * @group class:Psc\Code\Test\Mock\InvocationMockerBuilder
 */
class InvocationMockerBuilderTest extends \Psc\Code\Test\Base {
  
  protected $invocationMocker;
  protected $builder;
  
  public function setUp() {
    $this->chainClass = 'Psc\Code\Test\Mock\InvocationMockerBuilder';
    parent::setUp();
  }
  
  public function testAcceptance() {
    $this->builder = $this->getMockForAbstractClass('Psc\Code\Test\Mock\Builder', array($this, 'Psc\Doctrine\EntityRepository'));
    
    $this->invocationMocker = new InvocationMockerBuilder($this->builder, $this->once());
    
    // damit chainable vom builder wieder "zurückgehen" kann zum builder (da wir den chain durch expectsxxxx() in den kindklassen jweils verlassen)
    $this->assertSame($this->builder, $this->invocationMocker->end());

    // convenience wenn man nach dem letzten expectxxx() vergisst end() zu callen
    //$this->assertSame($this->builder, $this->invocationMocker->build());
    $this->assertSame($this->builder, $this->invocationMocker->getBuilder());
    
    // überprüfe ob unsere vererbung noch korrekt ist
    foreach (array('will','after','with','withAnyParameters','method') as $expectedMethod) {
      $this->assertTrue(method_exists($this->invocationMocker, $expectedMethod), $expectedMethod.'() existiert nicht');
    }
  }
}
?>