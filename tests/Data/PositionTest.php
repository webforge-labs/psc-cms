<?php

namespace Psc\Data;

/**
 * @group class:Psc\Data\Position
 */
class PositionTest extends \Psc\Code\Test\Base {
  
  protected $position;
  
  public function setUp() {
    $this->chainClass = 'Psc\Data\Position';
    parent::setUp();
    $this->position = new Position(0, 23);
  }
  
  public function testAcceptance() {
    $this->assertEquals(0, $this->position->getTop());
    $this->assertEquals(23, $this->position->getLeft());
  }
  
  public function testDefaultExportIsWithTopAndLeft() {
    $this->assertEquals((object) array('top'=>0,'left'=>23), $this->position->export());
  }
  
  public function testPositionIsSerializable() {
    $this->assertEquals($this->position, unserialize(serialize($this->position)));
  }
}
?>