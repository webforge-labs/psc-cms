<?php

namespace Psc\UI;

/**
 * @group class:Psc\UI\DropBox2
 */
class DropBox2Test extends \Psc\Code\Test\HTMLTestCase {
  
  protected $dropBox;
  
  public function setUp() {
    $this->chainClass = 'Psc\UI\DropBox2';
    parent::setUp();
    
    $this->tags = $this->loadTestEntities('tags');
    $this->dropBox = new DropBox2('tags',                                                    // name
                                  $this->getEntityMeta('Psc\Doctrine\TestEntities\Tag'),     // entityMeta für die items in der Box
                                  array($this->tags['t1'],$this->tags['t2']),                // values pre set (sind schon in der box)
                                  DropBox2::MULTIPLE                                         // flags
                                  );
  }
  
  public function testAcceptance() {
    $this->html = $this->dropBox->html();
    
    // dropbox mit den 2 tags darin
    $this->test->css('div.psc-cms-ui-drop-box', $this->html)
      ->count(1)
      ->test('button.psc-cms-ui-button.assigned-item')->count(2);
    ;
    
    $this->test->js($this->dropBox)
      ->constructsJoose('Psc.UI.DropBox')
        ->hasParam('name', $this->equalTo('tags'))
        ->hasParam('widget')
        ->hasParam('button0')
        ->hasParam('button1')
      ;
  }
  
  public function testDropBoxButtonsAreBridgedToJSWithFastItem() {
    $this->html = $this->dropBox->html();
    
    $button = $this->test->js($this->dropBox)
      ->constructsJoose('Psc.UI.DropBox')
        ->hasParam('button0')
        ->getParam('button0')
      ;
    
    $this->test->joose($button)
      ->constructsJoose('Psc.CMS.FastItem')
        ->hasParam('identifier', $this->equalTo($this->tags['t1']->getIdentifier()))
        ->hasParam('entityName', $this->equalTo('tag'))
    ;
  }
  
  public function testMultipleIsBrigedToJS() {
    $this->dropBox->setMultiple(TRUE);
    
    $this->html = $this->dropBox->html();
    
    $this->test->js($this->dropBox)
      ->constructsJoose('Psc.UI.DropBox')
        ->hasParam('multiple', $this->equalTo(true))
      ;
  }

  public function testConnectWithIsBrigedToJS() {
    $this->dropBox->setConnectWith('.other-dropboxes-on-page');
    
    $this->html = $this->dropBox->html();
    
    $this->test->js($this->dropBox)
      ->constructsJoose('Psc.UI.DropBox')
        ->hasParam('connectWith', $this->equalTo('.other-dropboxes-on-page'))
      ;
  }
}
?>