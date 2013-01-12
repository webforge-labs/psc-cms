<?php

namespace Psc\CMS\Service;

use Psc\Net\ServiceRequest;
use Psc\Net\Service;
use Psc\Code\Generate\GClass;

/**
 * @group service
 * @group class:Psc\CMS\Service\ControllerService
 */
class ControllerServiceTest extends \Psc\Code\Test\Base {
  
  protected $service;
  
  public function setUp() {
    $this->chainClass = 'Psc\CMS\Service\ControllerService';
    $this->project = $this->getMock('Psc\CMS\Project', array(), array(), '', FALSE);
    parent::setUp();
    $this->createControllerService();
  }
  
  public function testConstruct() {
    $this->assertChainable($this->service);
  }
  
  public function testIsResponsibleFor_succeeds() {
    $this->expectServiceRoutesController(array(new TestController(), 'test', array()));
    
    $this->assertTrue($this->service->isResponsibleFor(new ServiceRequest(Service::GET, array('success'))));
  }
  
  public function testIsResponsibleFor_neglects() {
    $this->expectServiceRoutesNot(1);
    
    $this->assertFalse($this->service->isResponsibleFor(new ServiceRequest(Service::GET, array('error'))));
  }
  
  public function testRoute_callsRightControllerMethod() {
    $controller = $this->getMock(__NAMESPACE__.'\\TestController', array('runMethod'));
    
    $this->expectServiceRoutesController(array($controller, 'runMethod', array('eins', 7)));
    
    $controller->expects($this->once())
               ->method('runMethod')
               ->with($this->equalTo('eins'),$this->equalTo(7));
    
    $this->service->route(new ServiceRequest(Service::GET, array('shouldBeRouted','this is not relevant')));
  }
  
  public function testRunController_convertsPDOException() {
    $this->service = $this->getMock('Psc\CMS\Service\ControllerService', array('routeController','setResponseFromException'), array($this->project));
    
    // controller throws an Exception
    $controller = $this->getMock(__NAMESPACE__.'\\TestController', array('pdoExceptionThrowingMethod'));
    $controller->expects($this->once())
              ->method('pdoExceptionThrowingMethod')
              ->will($this->throwException($pdoException = $this->doublesManager->createPDOUniqueConstraintException('my_unique_constraint','errorValue')));

    $doctrineException = \Psc\Doctrine\Exception::convertPDOException($pdoException);
    
    try {
      // run
      $this->service->runController($controller, 'pdoExceptionThrowingMethod', array());
    } catch (\Psc\Doctrine\UniqueConstraintException $e) {
      $this->assertEquals($doctrineException, $e);
      return;
    }
    
    $this->fail('Exception wurde nicht gecatched');
  }
  
  
  // @TODO testRunController_callsTransactionalBehaviour
  
  public function testRunController_respectsExceptionListenerReplacesException() {
    /* ein Listener der auf einer Exception lauscht */
    $listener = $this->getMock('Psc\ExceptionListener', array('listenException'));
    $listener->expects($this->once())
      ->method('listenException')
      ->with($this->isInstanceOf('ErrorException'))
      ->will($this->returnValue($evEx = new \Psc\Exception('elevated: Notice: var not set')));
      
    
    // controller throws an ErrorException
    $controller = $this->getMock(__NAMESPACE__.'\\TestController', array('errorExceptionThrowingMethod'));
    $controller->expects($this->once())
              ->method('errorExceptionThrowingMethod')
              ->will($this->throwException($errorException = new \ErrorException('Notice: var not set')));
              
    // subscribe
    $this->service->subscribeException('ErrorException', $listener);
              
    //run
    try {
      $this->service->runController($controller, 'errorExceptionThrowingMethod', array());
    } catch (\Psc\Exception $e) {
      $this->assertEquals($evEx, $e);
      return;
    }
    
    $this->fail('Es wurde keine Exception gecatched');
  }
  
  // @TODO testSetResponseFromException_subscribesToException
  public function testSubscribesException_andCallsListener() {
    /* ein Listener, der auf einer anderen Exception lautscht und damit ignoriert wird */
    $ignoredListener = $this->getMock('Psc\ExceptionListener', array('listenException'));
    $ignoredListener->expects($this->never())->method('listenException');
    
    /* ein Listener der auf der converteten Exception lauscht */
    $listener = $this->getMock('Psc\ExceptionListener', array('listenException'));
    $listener->expects($this->once())
      ->method('listenException')
      ->with($this->isInstanceOf('Psc\Doctrine\UniqueConstraintException'));

    // controller throws an PDOException
    $controller = $this->getMock(__NAMESPACE__.'\\TestController', array('pdoExceptionThrowingMethod'));
    $controller->expects($this->once())
              ->method('pdoExceptionThrowingMethod')
              ->will($this->throwException($pdoException = $this->doublesManager->createPDOUniqueConstraintException('my_unique_constraint','errorValue')));
              
    
    // subscribe          
    $this->service->subscribeException('Psc\Doctrine\UniqueConstraintException', $listener);
    $this->service->subscribeException('Psc\Doctrine\TooManyConnectionsException', $ignoredListener);
    
    // call
    try {
      $this->service->runController($controller, 'pdoExceptionThrowingMethod', array());
    } catch (\Psc\Doctrine\UniqueConstraintException $e) {
      return;
    }
    
    $this->fail('Es wurde keine Exception gecatched');
  }
  
  /**
   * @dataProvider provideConvertsResult
   */
  public function testRunController_convertsResult($expectedResult, $returnedResult) {
    // controller returns first Argument
    $controller = $this->getMock(__NAMESPACE__.'\\TestController', array('valueReturningMethod'));
    $controller->expects($this->once())
              ->method('valueReturningMethod')
              ->will($this->returnArgument(0));
              
    $this->service->runController($controller, 'valueReturningMethod', array($returnedResult));
    $this->assertInstanceof('Psc\Net\ServiceResponse',$res = $this->service->getResponse());
    $this->assertSame($expectedResult, $res->getBody());
    $this->assertEquals(\Psc\Net\Service::OK, $res->getStatus());
  }
  
  public static function provideConvertsResult() {
    $tests = array();
    $test = function ($controllerResult, $convertedResult) use (&$tests) {
      $tests[] = array($convertedResult, $controllerResult);
    };
    
    $testSame = function ($controllerResult) use (&$tests) {
      $tests[] = array($controllerResult, $controllerResult);
    };
    
    $testSame(array('with','nice','values'));
    $testSame(NULL);
    
    return $tests;
  }
  
  public function testGetControllerInstance() {
    $tc = __NAMESPACE__.'\\TestController';
    $this->assertInstanceOf($tc, $this->service->getControllerInstance(new \Psc\Code\Generate\GClass($tc)));
  }
  
  
  public function testSetGetProject() {
    $this->test->object = $this->service;
    $this->test->setter('project', NULL, \Psc\PSC::getProject());
  }
  
  public function testGetControllersNamespace() {
    $project = $this->getMock('Psc\CMS\Project', array('getNamespace'), array(), '', FALSE);
    $project->expects($this->once())
            ->method('getNamespace')
            ->will($this->returnValue('My\Entities\Namespace'));
            
    $this->service->setProject($project);
    $this->assertEquals('My\Entities\Namespace\Controllers', $this->service->getControllersNamespace());
  }

  public function testGetControllerClass() {
    $project = $this->getMock('Psc\CMS\Project', array('getNamespace'), array(), '', FALSE);
    $project->expects($this->any())->method('getNamespace')
            ->will($this->returnValue('My\Entities\Namespace'));
    
    $this->service->setProject($project);
    $this->assertEquals(new GClass('My\Entities\Namespace\Controllers\ConcreteEntityController'),
                        $this->service->getControllerClass('ConcreteEntity', FALSE)
                        );
    
    $service = $this->service;
    $this->assertException('Psc\CMS\Service\ControllerRouteException',
                           function () use ($service) {
                             $service->getControllerClass('ConcreteEntity'); // check = TRUE
                           }
                          );
  }

  /* Helpers */
  public function createControllerService() {
    $this->service = $this->getMock('Psc\CMS\Service\ControllerService', array('routeController','validateController'), array($this->project));
    
    return $this->service;
  }
  
  public function expectServiceRoutesController($to, $times = 1, $validation = TRUE) {
    $this->service->expects($this->exactly($times))->method('routeController')
      ->will($this->returnValue($to));

    $this->service->expects($this->atLeastOnce($times))->method('validateController')
      ->will($this->returnValue(TRUE));
  }
  
  public function expectServiceRoutesNot($times = 1) {
    $this->service
      ->expects($this->exactly($times))
      ->method('routeController')
      ->will($this->throwException(new \Psc\CMS\Service\ControllerRouteException('Das kann ich nicht')));
  }
}

class TestController {

}
?>