<?php

namespace Psc\Code\Test\Mock;

use Doctrine\ORM\EntityManager;

class RequestDispatcherBuilder extends \Psc\Code\Test\Mock\Builder {
  
  protected $responses;
  
  public function __construct(\Psc\Code\Test\Base $testCase) {
    parent::__construct($testCase, $fqn = 'Psc\URL\RequestDispatcher');
  }
  
  public function build() {
    //throw IncompleteBuildException::missingVariable('name');
    
    return $this->buildMock(array(
                              )
                           );
  }

  
  /**
   * @return InvocationMockerBuilder
   */
  public function expectReturnsResponseOnDispatch(\Psc\URL\Response $response, $matcher = NULL) {
    $this->responses[] = $response;
    
    return $this->buildExpectation('dispatch', $matcher)
      ->with($this->isInstanceOf('Psc\URL\Request'))
      ->will($this->returnCallback(array($this, 'returnResponse')));
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