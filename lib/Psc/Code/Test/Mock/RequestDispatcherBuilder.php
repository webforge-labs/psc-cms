<?php

namespace Psc\Code\Test\Mock;

use Doctrine\ORM\EntityManager;
use Psc\URL\Request;
use Psc\URL\Response;
use Psc\Net\HTTP\SimpleUrl;

class RequestDispatcherBuilder extends \Psc\Code\Test\Mock\Builder {
  
  protected $responses;
  
  public function __construct(\Psc\Code\Test\Base $testCase) {
    parent::__construct($testCase, $fqn = 'Psc\URL\RequestDispatcher');
    
  }
  
  public function build() {
    //throw IncompleteBuildException::missingVariable('name');
    
    return $this->buildMock(array(), $originalConstructor = TRUE);
  }

  
  /**
   *
   * @param callable $filter function (Psc\URL\Request $request, Psc\Net\HTTP\SimpleUrl $url with the url from request)
   * @deprecated
   * @return InvocationMockerBuilder
   */
  public function expectReturnsResponseOnDispatch(\Psc\URL\Response $response, $matcher = NULL, $filter = NULL) {
    $this->responses[] = $response;
    
    return $this->buildExpectation('dispatch', $matcher)
      ->with(
        $filter ? $this->logicalAnd($this->isInstanceOf('Psc\URL\Request'), $this->createRequestFilterCallback($filter))
                : $this->isInstanceOf('Psc\URL\Request')
      )
      ->will($this->returnCallback(array($this, 'returnResponse')));
  }
  
  /**
   * Use this for multiple dispatches with responses
   * 
   * order of calls is significant!
   * @param callable $filter function (Psc\URL\Request $request, Psc\Net\HTTP\SimpleUrl $url with the url from request)
   */
  public function expectReturnsResponse(Response $response, $filter) {
    return $this->buildAtMethodGroupExpectation('dispatch', 'default')
      ->with($this->logicalAnd($this->isInstanceOf('Psc\URL\Request'), $this->createRequestFilterCallback($filter)))
      ->will($this->returnValue($response))
    ;
  }
  
  protected function createRequestFilterCallback($filter) {
    return $this->callback(function (Request $request) use ($filter) {
      $simpleUrl = new SimpleUrl($request->getUrl());
      
      return $filter($request, $simpleUrl);
    });
  }
  
  /**
   * @return InvocationMockerBuilder
   */
  public function expectAuthenticationIsSetTo($user, $pw, $matcher = NULL) {
    return $this->buildExpectation('setAuthentication', $matcher)
      ->with($this->equalTo($user), $this->equalTo($pw))
      ->will($this->returnSelf());
  }
  
  public function returnResponse($request) {
    return array_shift($this->responses);
  }
}
?>