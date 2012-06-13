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
        ->hasParam('name')
        ->hasParam('widget')
      ;
  }
  
  public function testMultipleIsBrigedToJS() {
    $this->markTestIncomplete('TODO');
  }

  public function testConnectWithIsBrigedToJS() {
    $this->markTestIncomplete('TODO');
  }
}
?>