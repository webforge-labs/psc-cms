<?php

namespace Psc\Code\Test\Mock;

use Psc\Doctrine\TestEntities\Person;

/**
 * @group class:Psc\Code\Test\Mock\DoctrineEntityRepositoryBuilder
 */
class DoctrineEntityRepositoryBuilderTest extends \Psc\Code\Test\Base {
  
  protected $builder;
  
  public function setUp() {
    $this->chainClass = 'Psc\Code\Test\Mock\DoctrineEntityRepositoryBuilder';
    parent::setUp();
    $this->emm = $this->doublesManager->createEntityManagerMock();
    $this->builder = new DoctrineEntityRepositoryBuilder($this, $this->emm);
  }
  
  public function testAcceptance() {
    // $this->doublesManager->buildEntityRepository() == $this->builder
    $person = new Person('Me');
    $person->setIdentifier(7);

    $otherPerson = new Person('other');
    $otherPerson->setIdentifier(2);
    
    $repository = $this->builder
      ->setEntityName('Psc\Doctrine\TestEntities\Person')
      ->expectHydrates($person,$this->any())->end()
      ->expectSaves($otherPerson, $this->once())->end()
      ->build();
    
    $this->assertInstanceOf('PHPUnit_Framework_MockObject_MockObject', $repository);
    $this->assertInstanceOf('Psc\Doctrine\EntityRepository', $repository);
    
    $this->assertInstanceOf('Psc\Doctrine\EntityRepository',$repository->save($otherPerson));
    $this->assertSame($person,$repository->hydrate(7));
    $this->assertSame($person,$repository->hydrate(7));
  }
  
  public function testDeliverLog() {
    $log = array();
    
    $repository = $this->builder
      ->setEntityName('Psc\Doctrine\TestEntities\Person')
      ->expectLogsDeliverQuery($log, array(), $this->once())
      ->build();
    
    $repository->deliverQuery($query = $this->doublesManager->createQueryMock(array()), NULL, 'singleOrNULL');
    
    $expectedLog = array(
      array($query, NULL, 'singleOrNULL')
    );
    $this->assertEquals($expectedLog, $log);
  }
  
  /**
   * @expectedException Psc\Code\Test\Mock\IncompleteBuildException
   */
  public function testIncomplete() {
    $this->builder->build();
  }
}
?>