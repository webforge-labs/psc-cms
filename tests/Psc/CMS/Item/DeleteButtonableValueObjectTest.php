<?php

namespace Psc\CMS\Item;

class DeleteButtonableValueObjectTest extends ValueObjectTestCase {
  
  protected $buttonable;
  
  public function setUp() {
    $this->chainClass = 'Psc\\CMS\\Item\\DeleteButtonableValueObject';
    parent::setUp();
    
    $this->buttonable = new DeleteButtonableValueObject();
    $this->setButtonableTestValues($this->buttonable);
    $this->buttonable->setDeleteRequestMeta($this->deleteRequestMeta);
  }
  
  public function testCopyFromDeleteButtonableMakesACopyOfTheObject() {
    $copy = DeleteButtonableValueObject::copyFromDeleteButtonable($this->buttonable);
    
    $this->assertNotSame($this->buttonable, $copy);
    $this->assertEquals($this->buttonable, $copy);
  }
}
?>