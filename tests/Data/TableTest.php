<?php

namespace Psc\Data;

use Psc\Data\Type\Type;

/**
 * @group class:Psc\Data\Table
 */
class TableTest extends \Psc\Code\Test\Base {
  
  protected $table;
  
  public function setUp() {
    $this->chainClass = 'Psc\Data\Table';
    parent::setUp();
    $this->table = new Table(Array(
      Table::Column('num', Type::create('Integer')),
      Table::Column('ident', Type::create('String')),
      Table::Column('Sound', Type::create('String'))
    ));
  }
  
  public function testAcceptance() {
    $this->assertCount(3, $this->table->getColumns());
    
    $this->table->insertRow(
      array(1, '2-TEST-001', 'Dies ist ein kleiner TestSound')
    );
    
    $this->table->insertRow(
      array(2, '2-TEST-002', 'Dies ist ein weiterer TestSound')
    );
    
    $this->assertCount(2, $this->table->getRows());
  }
}
?>