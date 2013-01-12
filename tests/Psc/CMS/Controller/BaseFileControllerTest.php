<?php

namespace Psc\CMS\Controller;

use Psc\CMS\Controller\BaseFileController;
use Psc\CMS\Controller\Controller;

/**
 * @group class:Psc\CMS\Controller\BaseFileController
 */
class BaseFileControllerTest extends \Psc\Code\Test\Base {
  
  protected $c = '\Psc\CMS\Controller\BaseFileController';
  
    /**
   * @var \Psc\CMS\Controller\BaseFileController
   */
  protected $ctrl;

  public function setUp() {
    $this->ctrl = new BaseFileController('testing');
  }
  
  public function testConstruct() {
    $this->assertInternalType('array',$this->ctrl->getVars());
    $this->assertEmpty($this->ctrl->getVars(),'Vars muss leer sein');
  }

  /**
   */
  public function testFileNameException() {
    $this->assertEquals('ctrl.testing.php',$this->ctrl->getFileName());
  }

  /**
   * @expectedException \Psc\CMS\Controller\SystemException
   */
  public function testFileNameExceptionTricky() {
    $this->ctrl->setName(NULL);
    $this->ctrl->getFileName();
  }

  /**
   * @expectedException \Psc\CMS\Controller\SystemException
   */
  public function testFileNameExceptionHacky() {
    $ctrl = new HackyExtend('bla');
    $this->assertInstanceOf($this->c,$ctrl);
    
    $this->assertEquals('../../../etc/passwd',$ctrl->getFileName());
    
    $ctrl->init();
    
    try {
      $ctrl->run();
    } catch (\Exception $e) {
      $this->assertNotEquals(NULL,$ctrl->getName(),'run() darf name nicht zurücksetzen, nur replacen.');
      throw $e;
    }
  }
}

/* wenn man das hier allerdings schafft, könnte man getDirectory() auch hacken, sodass
   es eben doch geht. Aber wir gehen davon aus, dass wir uns eher gegen _GET - Sachen schützen müssen */
class HackyExtend extends BaseFileController {
  
  public function getFileName() {
    return '../../../etc/passwd';
  }
}
?>