<?php

namespace Psc\CMS;

class ActionMetaTest extends \Webforge\Code\Test\Base {
  
  public function setUp() {
    $this->chainClass = 'Psc\\CMS\\ActionMeta';
    parent::setUp();
    
    $this->actionMeta = new ActionMeta(ActionMeta::SPECIFIC, ActionMeta::GET, 'infos');
    $this->actionMetaWithoutSubresource = new ActionMeta(ActionMeta::SPECIFIC, ActionMeta::POST);
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
}
?>