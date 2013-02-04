<?php

namespace Psc\CMS\Item;

class ValueObjectTestCase extends \Webforge\Code\Test\Base {
  
  protected $deleteRequestMeta;
  protected $tabRequestMeta;
  protected $buttonLabel;
  protected $fullButtonLabel;
  protected $leftIcon;
  protected $rightIcon;
  
  public function setUp() {
    $this->chainClass = 'Psc\\CMS\\Item\\DeleteButtonableValueObject';
    parent::setUp();
    
    $this->deleteRequestMeta = $this->getMock('Psc\CMS\RequestMetaInterface');
    $this->tabRequestMeta = $this->getMock('Psc\CMS\RequestMetaInterface');
    $this->buttonLabel = 'a button';
    $this->fullButtonLabel = 'a button with a description';
    $this->leftIcon = NULL;
    $this->rightIcon = 'document';
  }
  
  protected function setButtonableTestValues($object) {
    $object->setButtonLabel($this->buttonLabel);
    $object->setFullButtonLabel($this->fullButtonLabel);
    $object->setButtonLeftIcon($this->leftIcon);
    $object->setButtonRightIcon($this->rightIcon);
  }
  
  public function testCopyFromDeleteButtonableMakesACopyOfTheObject() {
    $copy = DeleteButtonableValueObject::copyFromDeleteButtonable($this->buttonable);
    
    $this->assertNotSame($this->buttonable, $copy);
    $this->assertEquals($this->buttonable, $copy);
  }
}
?>