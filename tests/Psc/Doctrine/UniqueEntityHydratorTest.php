<?php

namespace Psc\Doctrine;

/**
 * @group class:Psc\Doctrine\UniqueEntityHydrator
 */
class UniqueEntityHydratorTest extends \Psc\Code\Test\Base {
  
  public function setUp() {
    $this->chainClass = 'Psc\Doctrine\UniqueEntityHydrator';
    parent::setUp();
    
    $this->log = array();
  }
  
  protected function createHydrator($entityShortName) {
    $repository = $this->doublesManager->buildEntityRepository('\Psc\Doctrine\TestEntities\\'.$entityShortName)
      ->setClassMetadata($this->getEntityMeta('Psc\Doctrine\TestEntities\\'.$entityShortName)->getClassMetadata())
      ->expectLogsDeliverQuery($this->log, array(), $this->any())
      ->build();
    
    return new UniqueEntityHydrator($repository);
  }
  
  public function testAcceptance() {
    $hydrator = $this->createHydrator('Tag');
    
    $hydrator->getEntity(array('id'=>5,'label'=>'RUS'));
    
    list($queryBuilder, $qbFunction, $return) = $this->log[0];
    $this->assertEquals('singleornull',mb_strtolower($return));
    
    // better ideas are welcome
    $this->assertEquals('SELECT entity FROM Psc\Doctrine\TestEntities\Tag entity WHERE entity.label = :label OR entity.id = :id',
                        $queryBuilder->getDQL()
                       );
  }
  
  public function testRegression_returnsNULLIfPrimaryIdIsNULL() {
    $hydrator = $this->createHydrator('Article');
    
    $this->assertNull($hydrator->getEntity(array('id'=>NULL)));
    $this->assertNull($hydrator->getEntity(array()));
  }
}
?>