<?php

namespace Psc\Data\Type;

use Psc\Code\Generate\GClass;

/**
 * @group class:Psc\Data\Type\EntityType
 */
class EntityTypeTest extends TestCase {
  
  protected $entityType;
  
  public function setUp() {
    $this->chainClass = 'Psc\Data\Type\EntityType';
    parent::setUp();
    $this->entityType = new EntityType(new GClass('Psc\Doctrine\TestEntities\Person'));
  }
  
  public function testAcceptance() {
    $this->assertTypeMapsComponent('ComboBox',$this->entityType);
  }
}
?>