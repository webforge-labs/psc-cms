<?php

namespace Psc\CMS\Controller;

/**
 * @group class:Psc\CMS\Controller\TPLController
 */
class TPLControllerTest extends \Psc\Code\Test\Base {
  
  public function setUp() {
    $this->chainClass = 'Psc\CMS\Controller\TPLController.php';
    parent::setUp();
    
  }
  
  public function testAcceptance() {
    $controller = new TPLController();
    
    $this->assertEquals('Hi there', $controller->get(array('simple')));
  }
}
?>