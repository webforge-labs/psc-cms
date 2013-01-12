<?php

namespace Psc\Doctrine;

/**
 * @group class:Psc\Doctrine\EntityRepository
 */
class EntityRepositoryDeliverQueryTest extends \Psc\Doctrine\RepositoryTest {
  
  public function setUp() {
    $this->entityClass = 'Psc\Doctrine\TestEntities\Tag';
    parent::setUp();
  }

  public function testWorksWithQuery() {
    $query = $this->doublesManager->createQueryMock(array('getResult','getSingleResult','getDQL'), $this->emm);
    $query->expects($this->once())->method('getResult')
          ->will($this->returnValue('thequeryresult'));
    
    $this->assertEquals('thequeryresult',
      $this->repository->deliverQuery($query, NULL, 'result')
    );
  }
  
  public function testonlyreturn() {
    $query = $this->doublesManager->createQueryMock(array('getResult','getSingleResult','getDQL'), $this->emm);
    $qb = $this->createQueryBuilderMock();
    
    $qbFunction = function ($qb) {
      return 'return';
    };
    
    $this->assertInstanceOf('Doctrine\ORM\QueryBuilder',
      $this->runDeliverQuery($qbFunction, 'result', $query, $qb)
    );
  }
  
  public function testgetResult() {
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

  public function testgetSingleResult() {
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

  public function testgetSingleResultOrNull_ReturnsNullOnEmptyResult() {
    $query = $this->doublesManager->createQueryMock(array('getResult','getSingleResult','getDQL'), $this->emm);
    $query->expects($this->once())->method('getSingleResult')
          ->will($this->throwException(new \Doctrine\ORM\NoResultException()));
    
    $this->assertEquals(NULL,
      $this->repository->deliverQuery($query, NULL, 'singleOrNULL')
    );
  }

  public function testgetSingleResultOrNull_ReturnsResultOnNONEmptyResult() {
    $query = $this->doublesManager->createQueryMock(array('getResult','getSingleResult','getDQL'), $this->emm);
    $query->expects($this->once())->method('getSingleResult')
          ->will($this->returnValue('thequerysingleresult'));
    
    $this->assertEquals('thequerysingleresult',
      $this->repository->deliverQuery($query, NULL, 'singleOrNULL')
    );
  }

  public function testgetScalarResult() {
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
  
  public function testemptyQBFunction_readsDefault() {
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
  public function testwithException_throwsPscDoctrineQueryException() {
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
  public function testwrongQBFunction() {
    $query = $this->doublesManager->createQueryMock(array('getResult','getScalarResult','getDQL'), $this->emm);
    $qb = $this->createQueryBuilderMock();
    $this->runDeliverQuery('blubb', 'returnResult', $query, $qb);
  }
  
  public function runDeliverQuery($qbFunction, $default, $query, $qb = NULL) {
    $qb = $qb ?: $this->createQueryBuilderMock($query);
    
    return $this->repository->deliverQuery($qb, $qbFunction, $default);  
  }
}
?>