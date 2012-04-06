<?php

namespace Psc\Doctrine;

use Psc\Code\Code;

class EnumTypeTest extends \Psc\Doctrine\DatabaseTest {
  
  public function setUp() {
    $this->con = 'tests';
    $this->chainClass = 'Psc\Doctrine\EnumType';
    parent::setUp();
  }
  
  public function testColumnDiff() {
    $sm = $this->em->getConnection()->getSchemaManager();
    
    $episodes = $sm->createSchema()->getTable('episodes');
    
    $status = $episodes->getColumn('status');
    $status2 = $episodes->getColumn('status');
    
    $comparator = new \Doctrine\DBAL\Schema\Comparator();
    $diff = $comparator->diffColumn($status, $status);
  }
  
}
?>