<?php

namespace Psc\CMS\Item;

class DropBoxButtonableValueObjectTest extends ValueObjectTestCase {
  
  public function setUp() {
    $this->chainClass = 'Psc\\CMS\\Item\\DropBoxButtonableValueObject';
    parent::setUp();

    $this->buttonable = new DropBoxButtonableValueObject();
    $this->setButtonableTestValues($this->buttonable);
    
    $this->buttonable->setIdentifier(7);
    $this->buttonable->setEntityName('Psc\Entities\Some');
    
    $this->buttonable->setTabRequestMeta($this->tabRequestMeta);
  }
  
  public function testCopyFromDeleteButtonableMakesACopyOfTheObject() {
    $copy = DropBoxButtonableValueObject::copyFromDropBoxButtonable($this->buttonable);
    
    $this->assertNotSame($this->buttonable, $copy);
    $this->assertEquals($this->buttonable, $copy);
  }  
}
?>