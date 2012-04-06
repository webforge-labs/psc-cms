<?php

namespace Psc\Doctrine;

class EntityRepositoryTest extends \Psc\Doctrine\DatabaseTest {
  
  protected $entityClass;
  protected $emm;
  protected $repository;
  
  public function setUp() {
    $this->con = 'tests';
    $this->chainClass = 'Psc\Doctrine\EntityRepository';
    parent::setUp();
    $this->emm = $this->getEntityManagerMock();
    $this->entityClass = 'Psc\Doctrine\TestEntities\Tag';
    $this->loadEntity($this->entityClass);
    $this->repository = $this->createRepository(NULL);
  }
  
  public function testConstruct() {
    $this->assertEquals($this->entityClass, $this->repository->getClassName());
    $this->assertAttributeSame($this->emm, '_em', $this->repository);
  }
  
  public function testConfigureUniqueConstraintValidator() {
    // wir brauchen einen Repository-Mock (wir self-stunnen getUniqueIndex
    $this->repository = $this->createRepository(array('getUniqueIndex'));
    
    // Tag Label Constraint
    $constraint = $this->createTagUniqueConstraint();
    
    // Daten (gleiche wie Fixture, aber nicht relevant)
    $dataRows = $this->createUQDataRows($constraint);
    
    // wir wollen, dass das self-stunned repository so tut als wäre dies ein Datenbank-Request gewesen
    $this->repository->expects($this->exactly(1))->method('getUniqueIndex')
                     ->with($this->isInstanceOf('Psc\Doctrine\UniqueConstraint'))
                     ->will($this->returnValue($dataRows));
    
    // Test
    $validator = $this->repository->configureUniqueConstraintValidator();
    $this->assertInstanceOf('Psc\Doctrine\UniqueConstraintValidator', $validator);
    
    // Assert: Unique Constraint(s) korrekt gesetzt?
    $this->assertEquals(array('tag_label'=>$constraint),
                        $validator->getUniqueConstraints()
                       );
    
    // Assert: Data
    // jede Row muss eine Exception schmeissen und diese soll korrekt gesetzt sein
    // dadurch wissen wir, dass der Index korrekt gefüllt ist
    foreach ($dataRows as $data) {
      $this->assertDataInIndex($validator, $data);
    }
  }
  
  protected function assertDataInIndex($validator, Array $data) {
    // pre
    $this->assertArrayHasKey('label', $data, 'Pre-Condition: Data hat kein Label gesetzt');
    $this->assertArrayHasKey('identifier', $data, 'Pre-Condition: Data hat keinen Identifier gesetzt');
    
    try {
      $validator->process($data);
      
      $this->fail('Es war eine UniqueConstraintException erwartet, aber keine wurde gecatched: '.Code::varInfo($data));
    } catch (UniqueConstraintException $e) {
      $this->assertEquals($e->uniqueConstraint, 'tag_label', 'unique constraint name ist falsch gesetzt');
      $this->assertEquals($e->duplicateKey, array('label'=>$data['label']), 'duplikate key daten sind falsch gesetzt');
      $this->assertEquals($e->duplicateIdentifier, $data['identifier'], 'identifier ist falsch gesetzt');
      return $e;
    }
  }
  
  public function testConfigureUniqueConstraintValidator_withGivenValidator() {
    // wir wollen, dass das self-stunned repository so tut als wäre dies ein Datenbank-Request gewesen
    $this->repository = $this->createRepository(array('getUniqueIndex'));
    $this->repository->expects($this->exactly(1))->method('getUniqueIndex')
                     ->with($this->isInstanceOf('Psc\Doctrine\UniqueConstraint'))
                     ->will($this->returnValue(array())); // tabelle ist leer


    $validator = new UniqueConstraintValidator($this->createTagUniqueConstraint());
    $returned = $this->repository->configureUniqueConstraintValidator($validator);
    $this->assertSame($validator, $returned);
  }
  
  
  public function testGetUniqueIndex() {
    $this->loadFixture('test_tags');
    
    // wir checken auch mit reihenfolge (dunno)
    $this->assertEquals($this->createUQDataRows($constraint = $this->createTagUniqueConstraint()),
                        $r = $this->repository->getUniqueIndex($constraint, function ($qb) {
                                                                              return $qb->addOrderBy('e.id','ASC');
                                                                            }
                                                              ),
                        "Index Return:\n".
                        "\n".
                        Helper::debugCollection($r)
                       );
  }
  
  public function testDeliverQuery_onlyreturn() {
    $query = $this->doublesManager->createQueryMock(array('getResult','getSingleResult','getDQL'), $this->emm);
    $qb = $this->createQueryBuilder();
    $qb->expects($this->never())->method('getQuery');
    
    $qbFunction = function ($qb) {
      return 'return';
    };
    
    $this->assertInstanceOf('Doctrine\ORM\QueryBuilder',
      $this->runDeliverQuery($qbFunction, 'result', $query, $qb)
    );
  }
  
  public function testDeliverQuery_getResult() {
    $query = $this->doublesManager->createQueryMock(array('getResult','getSingleResult','getDQL'), $this->emm);
    $query->expects($this->once())->method('getResult')
          ->will($this->returnValue('thequeryresult'));
    
    $qbFunction = function ($qb) {
      return 'result';
    };
    
    $this->assertEquals('thequeryresult',
      $this->runDeliverQuery($qbFunction, 'return', $query)
    );
  }

  public function testDeliverQuery_getSingleResult() {
    $query = $this->doublesManager->createQueryMock(array('getResult','getSingleResult','getDQL'), $this->emm);
    $query->expects($this->once())->method('getSingleResult')
          ->will($this->returnValue('thequerysingleresult'));
    
    $qbFunction = function ($qb) {
      return 'single';
    };
    
    $this->assertEquals('thequerysingleresult',
      $this->runDeliverQuery($qbFunction, 'return', $query)
    );
  }

  public function testDeliverQuery_getScalarResult() {
    $query = $this->doublesManager->createQueryMock(array('getResult','getScalarResult','getDQL'), $this->emm);
    $query->expects($this->once())->method('getScalarResult')
          ->will($this->returnValue('thequeryscalarresult'));
    
    $qbFunction = function ($qb) {
      return 'scalar';
    };
    
    $this->assertEquals('thequeryscalarresult',
      $this->runDeliverQuery($qbFunction, 'return', $query)
    );
  }
  
  public function testDeliverQuery_emptyQBFunction_readsDefault() {
    $query = $this->doublesManager->createQueryMock(array('getResult','getScalarResult','getDQL'), $this->emm);
    $query->expects($this->once())->method('getScalarResult')
          ->will($this->returnValue('thequeryscalarresult'));

    $qbFunction = NULL;
    
    $this->assertEquals('thequeryscalarresult',
      $this->runDeliverQuery($qbFunction, 'scalar', $query)
    );
  }
  
  /**
   * @expectedException Psc\Doctrine\QueryException
   */
  public function testDeliverQuery_withException_throwsPscDoctrineQueryException() {
    $query = $this->doublesManager->createQueryMock(array('getResult','getScalarResult','getDQL'), $this->emm);
    
    $query->expects($this->once())->method('getDQL')
          ->will($this->returnValue('SELECT * FROM DAM\AGE'));

    $query->expects($this->once())->method('getResult')
          ->will($this->throwException(\Doctrine\ORM\Query\QueryException::semanticalError('DAM\AGE ist keine Tabelle natürlich')));
    
    $this->runDeliverQuery(NULL, 'result', $query);
  }

  /**
   * @expectedException InvalidArgumentException
   */
  public function testDeliverQuery_wrongQBFunction() {
    $query = $this->doublesManager->createQueryMock(array('getResult','getScalarResult','getDQL'), $this->emm);
    $qb = $this->createQueryBuilder();
    $this->runDeliverQuery('blubb', 'returnResult', $query, $qb);
  }
  
  public function runDeliverQuery($qbFunction, $default, $query, $qb = NULL) {
    $qb = $qb ?: $this->createQueryBuilder($query);
    
    return $this->repository->deliverQuery($qb, $qbFunction, $default);  
  }
  
  protected function createTagUniqueConstraint() {
    return new UniqueConstraint('tag_label', array('label'=>$this->getType('String')));
  }
  
  protected function createQueryBuilder($query = NULL) {
    $queryBuilder = $this->doublesManager->createQueryBuilderMock(array('getQuery'), $this->emm);
    if (isset($query)) {
      $queryBuilder->expects($this->atLeastOnce())->method('getQuery') // respect getQuery()->getDQL() in exception
                   ->will($this->returnValue($query));
    }
    return $queryBuilder;
  }
  
  protected function createUQDataRows(UniqueConstraint $constraint) {
    $data = $constraint->getDataHelper();
    
    $rows = array();
    
    // das sind die labels aus dem fixture, für den testGetUniqueIndex test ist das wichtig(!) (für den anderen nicht)
    $rows[] = $data('migration', 1);
    $rows[] = $data('integration', 2);
    $rows[] = $data('php', 3);
    $rows[] = $data('audio', 4);
    $rows[] = $data('favorite', 5);
    $rows[] = $data('locked', 6);
    
    return $rows;
  }
  
  protected function createRepository(Array $methods = NULL) {
    return $this->emm->getRepositoryMock($this, $this->entityClass, $methods);
  }
}
?>