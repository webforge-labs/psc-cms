<?php

namespace Psc\UI;

class DropBox2Test extends \Psc\Code\Test\HTMLTestCase {
  
  protected $dropBox;
  
  public function setUp() {
    $this->chainClass = 'Psc\UI\DropBox2';
    parent::setUp();
    
    $this->markTestSkipped('TODO');
    $this->dropBox = new DropBox2();
  }
  
  public function testAcceptance() {
    $this->html = $this->dropBox->html();
    
    // dropbox
    $this->css->test('div.psc-cms-ui-drop-box', $this->html)
      ->count(1)
    ;
    
  }
}
?>