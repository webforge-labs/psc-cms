<?php

use \Psc\CMS\Controller\AjaxController
;

class AjaxControllerTest extends PHPUnit_Framework_TestCase {
  
  protected $ctrl;
  
  protected $c = '\Psc\CMS\Controller\AjaxController';
  
  public function setUp() {
    /* wir müssen $_GET und $_POST faken */
    $_GET = array ( 'todo' => 'tabs.content', 'ctrlTodo' => 'data', 'ajaxData' => array ( 'type' => 'oid', 'identifier' => '11602', ), );
    $_POST = array ( );
    
    $this->ctrl = new AjaxController(); // müsste dann ajax.general werden
  }
  
  /**
   * @expectedException \Psc\CMS\Controller\InvalidTodoException
   */
  public function testTodoException() {
    $this->ctrl->init();
  }

  /**
   * @expectedException \Psc\CMS\Controller\SystemException
   */
  public function testException() {
    $this->ctrl->setName('nichtvorhandenaufdiesemdateisytem');
    $this->testChainable();
  }

  public function testChainable() {
    $this->assertInstanceOf($this->c,$this->ctrl->addTodo('edit'));
    $this->assertInstanceOf($this->c,$this->ctrl->setTodo('edit'));
    
    $this->assertInstanceOf($this->c,$this->ctrl->addTodos(array('edit','delete','remove','tabs.content')));
    
    $this->assertInstanceOf($this->c,$this->ctrl->init());
    $this->assertInstanceOf($this->c,$this->ctrl->run());
  }
}

?>