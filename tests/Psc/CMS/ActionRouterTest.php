<?php

namespace Psc\CMS;

class ActionRouterTest extends \Webforge\Code\Test\Base {
  
  protected $router;
  protected $
  
  public function setUp() {
    $this->chainClass = 'Psc\\CMS\\ActionRouter';
    parent::setUp();

    $this->entity = new Tag('nice-label');
    $this->entityMeta = $this->getMockBuilder('Psc\CMS\EntityMeta')->disableOriginalConstructor()->getMock();
    
    $this->specificAction = new Action($this->entity, ActionMeta::POST, 'relevance');
    $this->generalAction = new Action($this->entityMeta, ActionMeta::GET, 'infos');
    
    $this->router = new ActionRouter();
    new Action($this->entity, ActionMeta::POST, 'relevance');
  }
  
  public function testMapsAnActionToRequestMeta() {
    
    
    $this->router
    
  }
}
?>