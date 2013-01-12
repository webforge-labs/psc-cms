<?php

namespace Psc\CMS\Item;

use Psc\CMS\RequestMeta;

/**
 * @group class:Psc\CMS\Item\Exporter
 */
class ExporterTest extends \Psc\Code\Test\Base {
  
  protected $exporter;
  
  public function setUp() {
    $this->chainClass = 'Psc\CMS\Item\Exporter';
    parent::setUp();
    
    $this->exporter = new Exporter();
    $this->article = current($this->loadTestEntities('articles'));
    $this->article->setId(17);
    $this->item = new Adapter($this->article, $this->getEntityMeta('Psc\Doctrine\TestEntities\Article'));
  }
  
  public function testTabOpenable() {
    $export = $this->exporter->TabOpenable($this->item);
    $this->assertArrayHasKey('tab', $export);
    
    $this->assertAttributeNotEmpty('label', $export['tab']);
    $this->assertAttributeNotEmpty('id', $export['tab']);
    $this->assertAttributeNotEmpty('url', $export['tab']);
  }
  
  public function testConvertToId() {
    $m = $this->getMock('Psc\CMS\Item\TabOpenable', array('getTabLabel', 'getTabRequestMeta'));
    $m->expects($this->any())->method('getTabRequestMeta')
      ->will($this->returnValue(
        new \Psc\CMS\RequestMeta('GET', '/entities/user/p.scheit@ps-webforge.com/form')
      ));
    $this->assertEquals('entities-user-p-scheit-at-ps-webforge-com-form',$this->exporter->convertToId($m));
  }

  public function testButtonable() {
    $export = $this->exporter->Buttonable($this->item);
    $this->assertArrayHasKey('button', $export);
    
    $this->assertAttributeNotEmpty('label', $export['button']);
    $this->assertAttributeNotEmpty('fullLabel', $export['button']);
    $this->assertAttributeEquals(Buttonable::DRAG, 'mode', $export['button']);
  }

  public function testAutoCompletable() {
    $export = $this->exporter->AutoCompletable($this->item);
    
    // performance felder für js autocomplete
    $this->assertArrayHasKey('label', $export);
    $this->assertArrayHasKey('value', $export);
    
    // api felder
    $this->assertArrayHasKey('ac', $export);
    $this->assertObjectHasAttribute('label',$export['ac'], $export);
  }
  
  public function testIdentifyable() {
    $export = $this->exporter->Identifyable($this->item);
    $this->assertGreaterThan(0, $export['identifier']);
    $this->assertArrayHasKey('entityName', $export);
  }
  
  public function testComboDropBoxable() {
    $export = $this->exporter->ComboDropBoxable($this->item);
    
    $this->assertArrayHasKey('button', $export);
    $this->assertArrayHasKey('ac', $export);
    $this->assertArrayHasKey('tab', $export);
    $this->assertArrayHasKey('identifier', $export);
    $this->assertArrayHasKey('entityName', $export);
  }
  
  public function testMerge() {
    $export = $this->exporter->merge($this->item, array('Identifyable','Buttonable','TabOpenable'));
    
    
    $this->assertArrayHasKey('button', $export);
    $this->assertArrayHasKey('entityName', $export);
    $this->assertArrayHasKey('tab', $export);
  }
}
?>