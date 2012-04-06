<?php

namespace Psc\Data\Type;

use Psc\Data\Type\CodeExporter;

class CodeExporterTest extends \Psc\Code\Test\Base {

  public function setUp() {
    $this->chainClass = 'Psc\Data\Type\CodeExporter';
    $this->exporter = $this->createCodeExporter();
    parent::setUp();
  }

  public function testConstruct() {
    $this->assertInstanceOf('Psc\Data\Type\Exporter', $this->createCodeExporter());
  }
  
  public function testExportBaseType() {
    $this->expectExport('new \Psc\Data\Type\IntegerType()',
                         new IntegerType()
                       );

    $this->expectExport('new \Psc\Data\Type\StringType()',
                         new StringType()
                       );
    
  }
  
  public function testCompositeType() {
    $this->expectExport('new \Psc\Data\Type\LinkType()',
                         new LinkType()
                       );
  }
  
  public function testExportEnclosingType() {
    $this->expectExport('new \Psc\Data\Type\ArrayType(new \Psc\Data\Type\IntegerType())',
                         new \Psc\Data\Type\ArrayType(new IntegerType())
                       );

    $this->expectExport("new \Psc\Data\Type\CollectionType(\Psc\Data\Type\CollectionType::PSC_ARRAY_COLLECTION, new \Psc\Data\Type\ObjectType(new \Psc\Code\Generate\GClass('Psc\\\\CMS\\\\SpecialEntity')))",
                         new \Psc\Data\Type\CollectionType(CollectionType::PSC_ARRAY_COLLECTION, new \Psc\Data\Type\ObjectType(new \Psc\Code\Generate\GClass('Psc\CMS\SpecialEntity')))
                       );
    
    $this->expectExport("new \Psc\Data\Type\PersistentCollectionType(new \Psc\Code\Generate\GClass('Psc\\\\CMS\\\\SpecialEntity'))",
                         new \Psc\Data\Type\PersistentCollectionType(new \Psc\Code\Generate\GClass('Psc\CMS\SpecialEntity'))
                       );
  }

  protected function expectExport($code, Type $type) {
    $this->assertEquals($code, $this->exporter->exportType($type));
  }

  public function createCodeExporter() {
    return new CodeExporter();
  }
}
?>