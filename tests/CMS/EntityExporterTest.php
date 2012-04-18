<?php

namespace Psc\CMS;

class EntityExporterTest extends \Psc\Code\Test\Base {
  
  protected $entityExporter;
  
  public function setUp() {
    $this->chainClass = 'Psc\CMS\EntityExporter';
    parent::setUp();
    $this->entityExporter = new EntityExporter();
  }
  
  public function testAcceptance() {
    $tags = $this->loadTestEntities('tags');
    
    $export = $this->entityExporter->autoComplete($tags['t1']);
    $this->assertInternalType('object', $export);
    $this->assertAttributeNotEmpty('label', $export);
    
    $export = $this->entityExporter->autoComplete($tags['t2'], EntityExporter::WITH_TAB);
    $this->assertAttributeInternalType('object', 'tab', $export);

    $export = $this->entityExporter->autoComplete($tags['t2'], EntityExporter::WITH_TCI);
    $this->assertAttributeInternalType('object', 'tci', $export);
  }
}
?>