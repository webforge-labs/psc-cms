<?php

namespace Psc\Code\Test\Mock;

use Doctrine\ORM\EntityManager;

class DoctrineEntityRepositoryBuilder extends \Psc\Code\Test\Mock\Builder {
  
  protected $em;
  
  protected $name;
  
  protected $classMetadata;
  
  public function __construct(\Psc\Code\Test\Base $testCase, EntityManager $em) {
    $this->em = $em;
    parent::__construct($testCase, $fqn = 'Psc\Doctrine\EntityRepository');
  }
  
  public function build() {
    if (!isset($this->name)) {
      throw IncompleteBuildException::missingVariable('name');
    }
    
    return $this->buildMock(array(
                              $this->em,
                              $this->classMetadata ?: $this->getMock('Doctrine\ORM\Mapping\ClassMetadata', array(), array($this->name))
                              )
                           );
  }

  
  /**
   * @return InvocationMockerBuilder
   */
  public function expectHydrates($entity, $matcher = NULL) {
    return $this->buildExpectation('hydrate', $matcher)
      ->with($this->equalTo($entity->getIdentifier()))
      ->will($this->returnValue($entity));
  }

  /**
   * @return InvocationMockerBuilder
   */
  public function expectFindsBy(Array $criterias, $result, $matcher = NULL) {
    return $this->buildExpectation('findBy', $matcher)
      ->with($this->equalTo($criterias,0,10,TRUE)) // order egal
      ->will($this->returnValue($result));
  }

  /**
   * Wird der Identifier angegeben schmeisst das Repository eine \Psc\Doctrine\EntityNotFoundException
   * 
   * @return InvocationMockerBuilder
   */
  public function expectDoesNotFind($identifier, $matcher = NULL) {
    return $this->buildExpectation('hydrate', $matcher)
      ->with($this->equalTo($identifier))
      ->will($this->throwException(
              \Psc\Doctrine\EntityNotFoundException::criteria(array('identifier'=>$identifier))
            ));
  }
  
  public function expectReturnsEntityManager($matcher = NULL) {
    return $this->buildExpectation('getEntityManager', $matcher)
      ->will($this->returnValue($this->em));
  }
  
  public function expectLogsDeliverQuery(Array &$log, $returnValue = NULL, $matcher = NULL) {
    return $this->buildExpectation('deliverQuery', $matcher)
      ->will($this->returnCallback(function ($queryBuilder, $qbFunction, $returnDefault) use (&$log, $returnValue) {
        $log[] = array($queryBuilder, $qbFunction, $returnDefault);
        return $returnValue;
      }));
  }

  /**
   * @return InvocationMockerBuilder
   */
  public function expectSaves($entity, $matcher = NULL) {
    return $this->buildExpectation('save', $matcher)
      ->with($this->equalTo($entity))
      ->will($this->returnSelf());
  }
  
  
  public function setClassMetadata(\Doctrine\Common\Persistence\Mapping\ClassMetadata $cmd) {
    $this->classMetadata = $cmd;
    return $this;
  }
  
  
  /**
   * @param FQN des Entities (wichtig, dass FQN)
   */
  public function setEntityName($name) {
    $this->name = $name;
    return $this;
  }
}
?>