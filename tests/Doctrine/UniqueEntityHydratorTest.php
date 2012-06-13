<?php

namespace Psc\Doctrine;

/**
 * @group class:Psc\Doctrine\UniqueEntityHydrator
 */
class UniqueEntityHydratorTest extends \Psc\Code\Test\Base {
  
  protected $uniqueEntityHydrator;
  
  public function setUp() {
    $this->chainClass = 'Psc\Doctrine\UniqueEntityHydrator';
    parent::setUp();
  }
  
  public function testAcceptance() {
    $log = array();
    
    $repository = $this->doublesManager->buildEntityRepository('\Psc\Doctrine\TestEntities\Tag')
      ->setClassMetadata($this->getEntityMeta('Psc\Doctrine\TestEntities\Tag')->getClassMetadata())
      ->expectLogsDeliverQuery($log, array(), $this->once())
      ->build();
    
    $hydrator = new UniqueEntityHydrator($repository);
    $hydrator->getEntity(array('id'=>5,'label'=>'RUS'));
    
    list($queryBuilder, $qbFunction, $return) = $log[0];
    $this->assertEquals('singleornull',mb_strtolower($return));
    
    // better ideas are welcome
    $this->assertEquals('SELECT entity FROM Psc\Doctrine\TestEntities\Tag entity WHERE entity.label = :label OR entity.id = :id',
                        $queryBuilder->getDQL()
                       );
  }
}
?>