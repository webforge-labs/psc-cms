<?php

namespace Psc\CMS;

/**
 * @group class:Psc\CMS\GridPanelColumn
 */
class GridPanelColumnTest extends \Psc\Code\Test\Base {
  
  protected $gridPanelColumn;
  
  public function setUp() {
    $this->chainClass = 'Psc\CMS\GridPanelColumn';
    parent::setUp();
    $this->col = new GridPanelColumn('id', $this->getType('Integer'), 'Nummer des Entities');
  }
  
  public function testAddClassesAddsToClasses() {
    $this->col->addClass('thin')->addClass('small');
    $this->assertArrayEquals(array('thin','small'), $this->col->getClasses());
    $this->col->removeClass('thin');
    $this->assertArrayEquals(array('small'), $this->col->getClasses());
    
    $this->col->setClasses(array('all'));
    $this->assertArrayEquals(array('all'), $this->col->getClasses());
  }
}
?>