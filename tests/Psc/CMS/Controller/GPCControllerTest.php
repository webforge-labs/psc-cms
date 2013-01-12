<?php

namespace Psc\CMS\Controller;

/**
 * @group class:Psc\CMS\Controller\GPCController
 */
class GPCControllerTest extends \Psc\Code\Test\Base {
  
  protected $ctrl;
  
  protected $c = '\Psc\CMS\Controller\GPCController';
  
  public function setUp() {
    $this->ctrl = new GPCController('testing');
  }

  /**
   * @expectedException \Psc\CMS\Controller\SystemException
   */
  public function testException() {
    $this->ctrl->setName('nichtvorhandenaufdiesemdateisytem');

    $this->assertInstanceOf($this->c,$this->ctrl->addTodo('edit'));
    $this->assertInstanceOf($this->c,$this->ctrl->setTodo('edit'));

    $_GET['todo'] = 'edit';
    $this->assertInstanceOf($this->c,$this->ctrl->init());
    $this->assertInstanceOf($this->c,$this->ctrl->run());
  }
}

?>