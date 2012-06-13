<?php

namespace Psc\CMS;

use Psc\UI\DropContentsList;

/**
 * @group class:Psc\CMS\RightContent
 */
class RightContentTest extends \Psc\Code\Test\Base implements DropContentsListCreater {
  
  protected $rightContent;
  protected $list;
  
  public function setUp() {
    $this->chainClass = 'Psc\CMS\RightContent';
    parent::setUp();
    
    $this->dc = $this->getMock('Psc\Doctrine\DCPackage',array('getEntityMeta'),array(),'',FALSE);
    
    $this->rightContent = new RightContent($this->dc);
  }
  
  public function testAcceptance() {
    $this->assertInstanceOf('Psc\CMS\DropContentsListPopulator', $this->rightContent);
  }
  
  public function testAddEntityLink() {
    $this->markTestIncomplete('@TODO');
  }

  public function testAddGridLink() {
    $this->markTestIncomplete('@TODO');
  }

  public function testAddSearchLink() {
    $this->markTestIncomplete('@TODO');
  }
  
  public function testPopulateLists() {
    $this->dc->expects($this->any())->method('getEntityMeta')->will($this->returnValue($this->getEntityMeta('user')));
    
    // welche items sollen standardmäßig hinzugefügt werden?
    $this->rightContent->populateLists($this);
    
    $this->assertNotEmpty($this->list);
  }
  
  public function newDropContentsList($label) {
    return $this->list = new DropContentsList();
  }
}
?>