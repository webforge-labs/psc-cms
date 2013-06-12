<?php

namespace Psc\CMS;

use Psc\UI\DropContentsList;
use Psc\Entities\User as UserEntity;

/**
 * @group class:Psc\CMS\RightContent
 */
class RightContentTest extends \Psc\Code\Test\HTMLTestCase implements DropContentsListCreater {
  
  protected $rightContent, $cmsRightContent;
  protected $list;
  
  public function setUp() {
    $this->chainClass = 'Psc\CMS\RightContent';
    parent::setUp();
    
    $this->dc = $this->getMock('Psc\Doctrine\DCPackage',array('getEntityMeta'),array(),'',FALSE);
    
    $this->cmsRightContent = new RightContent($this->dc, $this->getTranslationContainer());
    
    $this->dc->expects($this->any())->method('getEntityMeta')
             ->will($this->returnValue($this->getEntityMeta('user', $this->getProject()->getModule('Doctrine'))));
             
    $this->list =  $this->getMock('Psc\UI\DropContentsList');
    $this->cmsRightContent->populateLists($this); // this tests implements DropContentsListCreater
    
    $this->entity = new UserEntity('p.scheit@ps-webforge.com');
  }

  public function testAddEntityLink() {
    $this->rightContent = new RightContentWithEntityLink($this->dc, $this->getTranslationContainer());
    $this->list->expects($this->once())->method('addLinkable');
    
    $this->rightContent->populateLists($this);
  }

  public function testAddSearchPanelLink() {
    $this->rightContent = new RightContentWithSearchPanelLink($this->dc, $this->getTranslationContainer());
    $this->list->expects($this->once())->method('addLinkable');
    
    $this->rightContent->populateLists($this);
  }
  
  public function testAddGridLink() {
    $this->rightContent = new RightContentWithGridLink($this->dc, $this->getTranslationContainer());
    $this->list->expects($this->once())->method('addLinkable');
    
    $this->rightContent->populateLists($this);
  }

  public function testAddNewLink() {
    $this->rightContent = new RightContentWithNewLink($this->dc, $this->getTranslationContainer());
    $this->list->expects($this->once())->method('addLinkable');
    
    $this->rightContent->populateLists($this);
  }
  
  public function testGetAccordionWithListWithOpenAllIconHasaOpenAllIconInHTML() {
    $list = new DropContentsList();
    $list->setOpenAllIcon(TRUE);
    $list->add('testlink','/this/is/unrelevant','testlink-unique-id');
    
    $accordion = $this->cmsRightContent->getAccordion(Array(
      'Test-List-Header'=>$list
    ));
    
    $this->html = $accordion->html();
    
    // this is the same, as it is in javascript selected
    $this->test->css('.ui-accordion-header span.open-all-list')->count(1);
  }
  
  // interface: DropContentsListCreater
  public function newDropContentsList($label) {
    return $this->list;
  }
}

class RightContentWithGridLink extends \Psc\CMS\RightContent {
  
  public function populateLists(\Psc\CMS\DropContentsListCreater $creater) {
    $list = $creater->newDropContentsList('Links for USER Entity');
    
    $this->addGridLink($list, 'user');
  }
}

class RightContentWithEntityLink extends \Psc\CMS\RightContent {
  
  public $user;
  
  public function populateLists(\Psc\CMS\DropContentsListCreater $creater) {
    $list = $creater->newDropContentsList('Links for USER Entity');
    
    $this->addEntityLink($list, $this->user = new UserEntity('p.scheit@ps-webforge.com'));
  }
}

class RightContentWithNewLink extends \Psc\CMS\RightContent {
  
  public function populateLists(\Psc\CMS\DropContentsListCreater $creater) {
    $list = $creater->newDropContentsList('Links for USER Entity');
    
    $this->addNewLink($list, 'user');
  }
}

class RightContentWithSearchPanelLink extends \Psc\CMS\RightContent {
  
  public function populateLists(\Psc\CMS\DropContentsListCreater $creater) {
    $list = $creater->newDropContentsList('Links for USER Entity');
    
    $this->addSearchPanelLink($list, 'user');
  }
}
