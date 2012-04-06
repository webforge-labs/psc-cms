<?php

namespace Psc\Data;

use Psc\Data\Code;

class ArrayExportCollectionTest extends \Psc\Code\Test\Base {
  
  protected $arrayExportCollection;
  
  public function setUp() {
    $this->chainClass = 'Psc\Data\ArrayExportCollection';
    parent::setUp();
    
  }
  
  public function testAcceptance() {
    $this->arrayExportCollection = new ArrayExportCollection(array(
      new ExportWrapper('eins'),
      new ExportWrapper('zwei'),
    ));
    
    $this->assertInstanceOf('Psc\Data\Exportable', $this->arrayExportCollection);
    $this->assertEquals(array('eins','zwei'), $this->arrayExportCollection->export());
  }
  
  /**
   * @expectedException Psc\Data\Exception
   */
  public function testItemsMustBeExportable() {
    $collection = new ArrayExportCollection(array(new ExportWrapper('okay'),
                                                  'notOkay',
                                                  new ExportWrapper('okay')
                                                  )
                                            );
    $collection->export();
  }
}
?>