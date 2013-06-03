<?php

namespace Psc\Code\Test;

use Psc\Net\HTTP\Request;
use Psc\Net\Service;
use Closure;
use ReflectionClass;
use Psc\CMS\RequestMeta;
use Webforge\Common\System\Dir;
use Symfony\Component\HttpFoundation\Request AS SfRequest;

/**
 *
 * Klasse um immer wiederkehrende Elemente für Tests zu Erzeugen
 *
 *  - HTTPRequest (fake mit schönen Headern, user agent und so)
 *
 *  @TODO
 *    - TestEntityManager
 *    - TestMailer?
 */
class DoublesManager extends \Psc\Object {
  
  protected $testCase;
  
  public function __construct(Base $testCase) {
    $this->testCase = $testCase;
  }
  
  /**
   * @return Psc\Net\HTTP\Request
   */
  public function createHTTPRequest($methodString, $resource, $GET = array(), $POST = array(), $COOKIE = array(), $accept = NULL) {
    \Psc\Code\Code::value($methodString, 'GET','POST');
    
    $project = \Psc\PSC::getProject();
    $baseUrl = $project->getBaseURL();
    
    $SERVER = array (
                      'HTTP_HOST' => $baseUrl->getHost(),
                      'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 6.0; rv:7.0.1) Gecko/20100101 Firefox/7.0.1',
                      'HTTP_ACCEPT' => $accept ?: 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                      'HTTP_ACCEPT_LANGUAGE' => 'en-us,en;q=0.5',
                      'HTTP_ACCEPT_ENCODING' => 'gzip, deflate',
                      'HTTP_ACCEPT_CHARSET' => 'ISO-8859-1,utf-8;q=0.7,*;q=0.7',
                      'HTTP_CONNECTION' => 'keep-alive',
                      'SERVER_NAME' => $baseUrl->getHost(),
                      'DOCUMENT_ROOT' => (string) $project->getHtdocs(),
                      'REDIRECT_QUERY_STRING' => 'request='.$resource,
                      'REDIRECT_URL' => $resource,
                      'GATEWAY_INTERFACE' => 'CGI/1.1',
                      'SERVER_PROTOCOL' => 'HTTP/1.1',
                      'REQUEST_METHOD' => $methodString,
                      'QUERY_STRING' => 'request='.$resource,
                      'REQUEST_URI' => $resource,
                      'SCRIPT_NAME' => '/api.php',
                      'PHP_SELF' => '/api.php',
                      'REQUEST_TIME' => time()
                    );
    
    //$request = create($uri, $method = 'GET', $parameters = array(), $cookies = array(), $files = array(), $server = array(), $content = null)
    $request = SfRequest::create((string) $baseUrl.'/'.ltrim($resource,'/'),
                                 $methodString,
                                 $methodString === 'POST' ? $POST : $GET,
                                 $COOKIE,
                                 array(),
                                 $SERVER
                                 );
    
    $request = Request::infer($request);
    
    return $request;
  }
  
  /*
   * Gibt einen ServiceRequest zurück
   * 
   * @return Psc\Net\ServiceRequest
   */
  public function createRequest($method = Service::GET, Array $parts, $body = NULL, Array $query = NULL) {
    if ($method === NULL) $method = Service::GET;
    return new \Psc\Net\ServiceRequest($method, $parts, $body, $query);
  }
  
  public function createPDOUniqueConstraintException($keyName = 'sound_number_ravensburger', $causingEntry = '2-STA_0802') {
    $e = new \PDOException("SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry '".$causingEntry."' for key '".$keyName."'");
    $e->errorInfo = array(0=>'23000',
                          1=>1062, 
                          2=>"Duplicate entry '".$causingEntry."' for key '".$keyName."'"
                        );
    return $e;
  }

  public function createFileMock() {
    return $this->testCase->getMock('Webforge\Common\System\File', NULL, array('/tmp/empty/not/Read'));
  }
  
  /**
   * $this->doublesManager->createTemplateMock('willkommen im psc-cms', $this->once());
   */
  public function createTemplateMock($content = NULL, \PHPUnit_Framework_MockObject_Matcher_InvokedCount $expectation = NULL) {
    $template = $this->testCase->getMock('Psc\TPL\Template', array('get'), array(),'',FALSE);
    
    $expectation = $expectation ?: $this->testCase->any();
    
    if (func_num_args() >= 1) {
      $template->expects($expectation)
           ->method('get')
           ->will($this->testCase->returnValue($content));
    }
    
    return $template;
  }
  
  public function createEntityManagerMock(\Psc\Doctrine\Module $module = NULL, $con = NULL) {
    if (isset($module)) {
      if (!isset($con)) $con = $module->getConnectionName();
      
      $evm = new \Doctrine\Common\EventManager();
      $config = $module->getConfiguration();
      
      $conn = \Doctrine\DBAL\DriverManager::getConnection(
        $module->getConnectionOptions($con), $config, $evm
      );

      return new \Psc\Doctrine\Mocks\EntityManager($conn,$config,$evm);
    } else {
      return new \Psc\Doctrine\Mocks\EntityManager();
    }
  }
  
  public function createDoctrineModuleMock() {
    return $this->testCase->getMockBuilder('Psc\Doctrine\Module')->disableOriginalConstructor()->getMock();
  }
  
  public function createDoctrinePackageMock(\Psc\Doctrine\Module $module = NULL, \Doctrine\ORM\EntityManager $entityManager = NULL) {
    $dc = $this->testCase->getMock(
            'Psc\Doctrine\DCPackage',
            array(),
            array(
              $module = $module ?: $this->createDoctrineModuleMock(),
              $entityManager = $entityManager ?: $this->createRealEntityManagerMock()
            )
          );
    
    $dc->expects($this->any())->method('getEntityManager')->will($this->returnValue($entityManager));
    $dc->expects($this->any())->method('getModule')->will($this->returnValue($module));
    
    return $dc;
  }

  public function createRealEntityManagerMock() {
    return $this->testCase->getMockBuilder('Doctrine\ORM\EntityManager')->disableOriginalConstructor()->getMock();
  }
  
  public function createEntityMetaProvider(Array $entityMetas = array()) {
    $mock = $this->testCase->getMockForAbstractClass('Psc\CMS\EntityMetaProvider');
    
    foreach ($entityMetas as $entityMeta) {
      $mock->expects($this->any())
        ->method('getEntityMeta')
        ->with(
          $this->testCase->logicalOr(
            $this->testCase->equalTo($entityMeta->getEntityName()),
            $this->testCase->equalTo($entityMeta->getClass())
          )
        )
        ->will($this->testCase->returnValue($entityMeta))
      ;
    }
    
    return $mock;
  }
  
  /**
   * Wird der 2te Parameter weggelassen (EntityManager) wird das Repository mit einem EntityManagerMock constructed
   *
   *
   * $this->entityRepository = $this->doublesManager->buildEntityRepository('Entities\User')
      ->expectHydrates($user,$this->once())
      ->build();
   */
  public function buildEntityRepository($entityName, \Doctrine\ORM\EntityManager $em = NULL) {
    $builder = $this->createBuilder('DoctrineEntityRepository', array($em ?: $this->createEntityManagerMock()));
    $builder->setEntityName($entityName);
    
    return $builder;
  }
  
  /**
   *
   * $dbm = $this->doublesManager;
   * $dispatcher = $dbm->RequestDispatcher()
   *  ->expectReturnsResponseOnDispatch(
   *     $this->iniResponse = $dbm->createURLResponse()
   *   )
   *  ->build();
   * @return Psc\Code\Test\Mock\RequestDispatcherBuilder
   */
  public function RequestDispatcher() {
    return $this->createBuilder('RequestDispatcher', array());
  }
  
  /**
   * @return Psc\URL\Response
   */
  public function createURLResponse($raw, Array $headers = array()) {
    return new \Psc\URL\Response($raw, new \Psc\URL\HTTP\Header($headers));
  }
  
  public function Project($name, Dir $root, Array $paths = NULL) {
    $builder = $this->createBuilder('Project', array($name, $root, $paths));
    
    return $builder;
  }
  
  public function createBuilder($mockName, Array $args) {
    $class = sprintf('Psc\Code\Test\Mock\%sBuilder',$mockName);
    
    array_unshift($args, $this->testCase);
    $refl = new ReflectionClass($class);
    return $refl->newInstanceArgs($args);
  }
  
  public function createQueryMock($methods, \Doctrine\ORM\Entitymanager $em = NULL, $callOriginalConstructor = TRUE) {
    
    if (is_array($methods)) {
      $methods = array_merge($methods, array('_doExecute','getSQL'));
    }
    // query selbst ist nicht mockbar
    return $this->testCase->getMock('Doctrine\ORM\AbstractQuery', $methods, array($em ?: $this->createEntityManagerMock()), '', $callOriginalConstructor);
  }

  public function createQueryBuilderMock($methods, \Doctrine\ORM\Entitymanager $em, $callOriginalConstructor = TRUE) {
    return $this->testCase->getMock('Doctrine\ORM\QueryBuilder', $methods, array($em), '', $callOriginalConstructor);
  }
  
  public function createValuesMock($classFQN, Array $methodValues, $constraint = NULL) {
    if (!isset($constraint)) $constraint = $this->testCase->once();
    $mock = $this->testCase->getMock($classFQN, array_keys($methodValues));
    foreach ($methodValues as $method => $value) {
      $mock->expects($constraint)->method($method)->will($this->testCase->returnValue($value));
    }
    return $mock;
  }

  public function createTabOpenable($url, $label, $method = 'GET', $constraint = NULL) {
    if (!isset($constraint)) $constraint = $this->testCase->atLeastOnce();
    return $this->createValuesMock(
              'Psc\CMS\Item\TabOpenable',
              Array(
                'getTabLabel'=>$label,
                'getTabRequestMeta'=>new RequestMeta($method, $url)
              ),
              $constraint
           );
  }
  
  protected function returnValue($value) {
    return $this->testCase->returnValue($value);
  }
  
  protected function any() {
    return $this->testCase->any();
  }
  
  protected function once() {
    return $this->testCase->once();
  }
}
?>