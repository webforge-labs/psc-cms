<?php

namespace Psc\CMS\Controller;

use Psc\CMS\Controller\BaseController;
use Psc\CMS\Controller\Controller;

/**
 * @group class:Psc\CMS\Controller\BaseController
 */
class BaseControllerTest extends \Psc\Code\Test\Base {
  
  /**
   * @var \Psc\CMS\Controller\BaseController
   */
  protected $ctrl;
  
  protected $c = '\Psc\CMS\Controller\BaseController';
  
  public function setUp() {
    $this->ctrl = new BaseControllerDummy();
  }
  
  public function testConstruct() {
    $this->assertInternalType('array',$this->ctrl->getVars());
    $this->assertEmpty($this->ctrl->getVars(),'Vars muss leer sein');
  }
  
  /**
   * @depends testConstruct
   */
  public function testTodo() {
    $this->ctrl->addTodo('edit');
    $this->ctrl->setTodo('edit');
    
    $this->ctrl->setTodo('banane');
    $this->assertNotEquals('banane',$this->ctrl->getTodo());
    
    $this->ctrl->addTodo('alias_for_edit','edit');
    $this->ctrl->setTodo('alias_for_edit');
    $this->assertNotEquals('alias_for_edit',$this->ctrl->getTodo());
    $this->assertEquals('edit',$this->ctrl->getTodo());
  }

  public function testInitMasks() {
    foreach (array(Controller::INIT_TODO) AS $bit) {
      $mode = Controller::MODE_NORMAL & ~$bit;
      
      $this->assertTrue((bool) (Controller::MODE_NORMAL & $bit)); // maskentest
      $this->assertFalse((bool) ($mode & $bit)); // maskentest  
    }
  }
  
  
  public function testChainable() {
    $this->assertInstanceOf($this->c,$this->ctrl->addTodo('edit'));
    $this->assertInstanceOf($this->c,$this->ctrl->setTodo('edit'));
    
    $this->assertInstanceOf($this->c,$this->ctrl->addTodos(array('edit','delete','remove')));
    
    $this->assertInstanceOf($this->c,$this->ctrl->init());
    $this->assertInstanceOf($this->c,$this->ctrl->run());
  }
  
  /**
   * @depends testConstruct
   */
  public function testOthers() {
    $this->ctrl->init();
    
    $this->ctrl->addTodo('remove','del','delete');
    $this->ctrl->setTodo('remove');
    
    $this->ctrl->getTodoURL();
    $this->ctrl->getTodoURL('remove');
    
    $this->ctrl->getTodoURL('nothere'); // hier ist nicht klar, ob des gehen soll
  }
}

class BaseControllerDummy extends BaseController {
  
  public $info = array('initTodo'=>FALSE,
                       ''
                       );
  
  public function __construct() {
    parent::__construct();
  }
  
  public function construct() {
    parent::construct();
    $this->info['construct'] = TRUE;
  }
  
  public function init($mode = Controller::MODE_NORMAL) {
    return $this;
  }
  
  public function run() {
    return $this;
  }
}
?>