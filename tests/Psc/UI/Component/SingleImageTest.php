<?php

namespace Psc\UI\Component;

/**
 * @group class:Psc\UI\Component\SingleImage
 */
class SingleImageTest extends \Psc\Code\Test\Base {
  
  protected $singleImage;
  
  public function setUp() {
    $this->chainClass = 'Psc\UI\Component\SingleImage';
    parent::setUp();
    //$this->singleImage = new SingleImage();
  }
  
  public function testAcceptance() {
    $this->markTestIncomplete('TODO better components tests');
  }
}
?>