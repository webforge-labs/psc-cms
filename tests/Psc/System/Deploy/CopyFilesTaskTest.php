<?php

namespace Psc\System\Deploy;

/**
 * @group class:Psc\System\Deploy\CopyFilesTask
 */
class CopyFilesTaskTest extends \Psc\Code\Test\Base {
  
  protected $copyFilesTask;
  
  public function setUp() {
    $this->chainClass = 'Psc\System\Deploy\CopyFilesTask';
    parent::setUp();
    
    //$this->copyFilesTask = new CopyFilesTask();
  }
  
  public function testAcceptance() {
    
    
    $task = new CopyFilesTask($tiptoi->getSrc(), $target->getSrc(), $fileList);
    
    $this->markTestIncomplete('TODO der Test ist etwas lächerlich');
  }
}
?>