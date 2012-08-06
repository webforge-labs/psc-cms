<?php

namespace Psc\Data;

/**
 * @group class:Psc\Data\Dimension
 */
class DimensionTest extends \Psc\Code\Test\Base {
  
  protected $dimension;
  
  public function setUp() {
    $this->chainClass = 'Psc\Data\Dimension';
    parent::setUp();
    $this->dimension = new Dimension(400, 300);
  }
  
  public function testAcceptance() {
    $this->assertInstanceOf('Psc\Data\ValueObject', $this->dimension);
    $this->assertEquals(400, $this->dimension->getWidth());
    $this->assertEquals(300, $this->dimension->getHeight());
  }

  public function testDefaultExportIsWithWidthAndHeight() {
    $this->assertEquals((object) array('width'=>400,'height'=>300), $this->dimension->export());
  }
  
  public function testIsSerializable() {
    $this->assertEquals($this->dimension, unserialize(serialize($this->dimension)));
  }
}
?>