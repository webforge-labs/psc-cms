<?php

namespace Psc\Data;

class ExportWrapperTest extends \Psc\Code\Test\Base {
  
  protected $exportWrapper;
  
  public function setUp() {
    $this->chainClass = 'Psc\Data\ExportWrapper';
    parent::setUp();
    
  }
  
  public function testAcceptance() {
    $this->exportWrapper = new ExportWrapper('string');
    $this->assertInstanceOf('Psc\Data\Exportable', $this->exportWrapper);
    $this->assertEquals('string', $this->exportWrapper->export());
  }
}
?>