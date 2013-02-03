<?php

namespace Psc\CMS;

class ActionMetaTest extends \Webforge\Code\Test\Base {
  
  public function setUp() {
    $this->chainClass = 'Psc\\CMS\\ActionMeta';
    parent::setUp();
    
    $this->specificMeta = $this->actionMeta = new ActionMeta(ActionMeta::SPECIFIC, ActionMeta::GET, 'infos');
    $this->generalMeta = new ActionMeta(ActionMeta::GENERAL, ActionMeta::GET, 'list');
    $this->actionMetaWithoutsubResource = new ActionMeta(ActionMeta::SPECIFIC, ActionMeta::POST);
  }
  
  public function testIsGeneralReturnsTrueIfTypeIsGeneral() {
    $this->assertTrue($this->generalMeta->isGeneral());
  }

  public function testIsSpecificReturnsTrueIfTypeIsSpecific() {
    $this->assertTrue($this->specificMeta->isSpecific());
  }
  
  public function testMetaActionCanBeConstructedWithOutAnEntityOrEntityMeta() {
    $this->assertEquals(ActionMeta::SPECIFIC, $this->actionMeta->getType());
    $this->assertEquals(ActionMeta::GET, $this->actionMeta->getVerb());
    $this->assertEquals('infos', $this->actionMeta->getSubResource());
  }
  
  public function testMetaActionCannotBeConstructedWithInvalidType() {
    $this->setExpectedException('Psc\Code\WrongValueException');
    
    new ActionMeta('notValidType', ActionMeta::GET);
  }
  
  public function testMetaActionCannotBeConstructedWithInvalidVerb() {
    $this->setExpectedException('Psc\Code\WrongValueException');
    
    new ActionMeta(ActionMeta::GENERAL, 'notValidVerb');
  }
  
  public function testHassubResourceReturnsFalseIfMetaHasNosubResource() {
    $this->assertFalse($this->actionMetaWithoutsubResource->hasSubResource());
  }

  public function testHassubResourceReturnsTrueIfMetaHassubResource() {
    $this->assertTrue($this->specificMeta->hasSubResource());
  }
}
?>