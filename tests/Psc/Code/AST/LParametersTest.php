<?php

namespace Psc\Code\AST;

/**
 * @group class:Psc\Code\AST\LParameters
 */
class LParametersTest extends \Psc\Code\Test\Base {
  
  protected $lParameters;
  
  public function setUp() {
    $this->chainClass = 'Psc\Code\AST\LParameters';
    parent::setUp();
    $this->lParameters = new LParameters(array($this->sounds = new LParameter('sounds', new LType('Array')), $this->options = new LParameter('options', new LType('Object'))));
  }
  
  public function testHasParameter() {
    $this->assertTrue($this->lParameters->hasParameter('sounds'));
    $this->assertFalse($this->lParameters->hasParameter('soundssss'));
    $this->assertTrue($this->lParameters->hasParameter('options'));
    $this->assertTrue($this->lParameters->hasParameter($this->sounds));
    $this->assertTrue($this->lParameters->hasParameter($this->options));
    $this->assertFalse($this->lParameters->hasParameter(new LParameter('identifier', new LType('Integer'))));
  }
  
  public function tesetRemoveParameter() {
    $this->assertChainable($this->lParameters->removeParameter($this->sounds));
    $this->assertFalse($this->lParameters->hasParameter($this->sounds));
  }
  
  public function testTraversable() {
    $names = array();
    foreach ($this->lParameters as $parameter) {
      $names[] = $parameter->getName();
    }
    $this->assertEquals(array('sounds','options'), $names);
  }
  
  public function testAddParameter() {
    $this->assertChainable($this->lParameters->addParameter($p = new LParameter('identifier', new LType('Integer'))));
    $this->assertTrue($this->lParameters->hasParameter('identifier'));
  }
  
  public function testGetParameter() {
    $this->assertSame($this->sounds, $this->lParameters->getParameter('sounds'));
  }
  
  public function testGetParameterException() {
    $this->setExpectedException('Psc\Exception');
    $this->lParameters->getParameter('soundsss');
  }
}
?>