<?php

namespace Psc\Code\Test;

use Psc\Code\Code;

class CMSAcceptanceTester extends \Psc\System\LoggerObject {
  
  /**
   * @var Psc\Code\Test\Base
   */
  protected $testCase;
  
  /**
   * @var Psc\URL\Response
   */
  protected $lastResponse;
  
  /**
   * @var Psc\CMS\EntityMeta
   */
  protected $entityMeta;
  
  /**
   * @var string
   */
  protected $urlPrefix = '/entities';

  /**
   * @var bool
   */
  protected $debug = FALSE;
  
  public function __construct(Base $testCase, $entityName, $debug = FALSE) {
    $this->testCase = $testCase;
    $this->entityMeta = $this->getEntityMeta($entityName);
    $this->setDebug($debug);
    $this->getLogger()->setPrefix('[acceptance]');
  }
  
  /**
   * $this->test->acceptance($entityName)->save($id, $post)
   */
  public function save($id, $post, $subResource = NULL) {
    $post = $this->parsePost($post);
    
    $dispatcher = $this->dispatcher('PUT', $this->urlPrefix.'/'.$this->entityMeta->getEntityName().'/'.$id.($subResource ? '/'.$subResource : NULL), 'json');
    $dispatcher->setRequestData($post);
    
    return $this->result($dispatcher, 'json');
  }
  
  public function insert($post, $response = 'json') {
    $post = $this->parsePost($post);
    
    $dispatcher = $this->dispatcher('POST', $this->urlPrefix.'/'.$this->entityMeta->getEntityNamePlural(), 'json');
    $dispatcher->setRequestData($post);
    
    return $this->result($dispatcher, $response);
  }

  public function delete($id) {
    $dispatcher = $this->dispatcher('DELETE', $this->urlPrefix.'/'.$this->entityMeta->getEntityName().'/'.$id, 'json');
    
    return $this->result($dispatcher, 'json');
  }
  
  /**
   * Testet ein komplettes löschen (PREHA Prinzip):
   * 
   * - prüft ob das entity mit der $id vorhanden ist
   * - löscht das entity
   * - cleart den $em
   * - asserted dass das json die alte id zurückgibt
   * - asserted dass das entity in der datenbank gelöscht wurde
   *
   * gibt das JSON zurück
   * @return string
   */
  public function deleteAcceptance($id, \Doctrine\ORM\EntityManager $em) {
    // Prepare
    $idField = $this->entityMeta->getIdentifier()->getName();
    $criteria = array($idField=>$id);
    $repository = $em->getRepository($this->entityMeta->getClass());
    
    $res = $repository->findBy($criteria);
    $this->testCase->assertCount(1, $res, 'pre-condition: '.$this->entityMeta->getEntityName().' ist vorhanden mit der '.$idField.' '.$id.' - failed.');
    
    // Run
    $json = $this->delete($id);
    // gibt die gelöschte oid zurück?
    $this->testCase->assertEquals($id, $json->id, $idField.' war nicht korrekt in JSON-Response gesetzt');
    
    // E[rase] from memory
    $em->clear();
    
    // hydrate
    $res = $repository->findBy($criteria);
    
    // assert
    $this->testCase->assertEquals(array(), $res, 'Entity '.$this->entityMeta->getEntityName().':'.$idField.'=>'.$id.' darf nicht mehr vorhanden sein');
    
    return $json;
  }
  
  /**
   * Ruft den SearchPanel (Autocomplete mit Tab öffnen) auf
   *
   */
  public function searchPanel() {
    $dispatcher = $this->dispatcher('GET', $this->urlPrefix.'/'.$this->entityMeta->getEntityNamePlural().'/search');
    
    return $this->result($dispatcher, 'html');
  }
  
  /**
   * $this->test->acceptance($entityName)->autoComplete()
   *
   * @return array $json
   */
  public function autoComplete($term = '', $url = NULL, $maxResults = 15) {
    $acRequestMeta = $this->entityMeta->getAutoCompleteRequestMeta();
    $url = $url ?: $acRequestMeta->getUrl(array());
    
    $dispatcher = $this->dispatcher($acRequestMeta->getMethod(), $url, 'json');
    $dispatcher->setRequestData(array('autocomplete'=>'true', 'search'=>$term, 'maxResults'=>$maxResults));
    
    $result = $this->result($dispatcher, 'json');
    
    $this->testCase->assertLessThanOrEqual($maxResults, count($result), 'Maximal 15 Results duerfen hier im Ergebnis sein. MaxResults  wurde verletzt');
    return $result;
  }
  
  /**
   * @return CMSFormAcceptanceTester
   */
  public function form($id = NULL, $subResource = NULL) {
    if ($id === NULL) {
      $dispatcher = $this->dispatcher('GET', $this->urlPrefix.'/'.$this->entityMeta->getEntityNamePlural().'/form'.($subResource ? '/'.$subResource : NULL));
    } else {
      $dispatcher = $this->dispatcher('GET', $this->urlPrefix.'/'.$this->entityMeta->getEntityName().'/'.$id.'/form'.($subResource ? '/'.$subResource : NULL));
    }
    
    return new CMSFormAcceptanceTester($this->result($dispatcher, 'response'), $this);
  }

  /**
   * @return CMSFormAcceptanceTester
   */
  public function customForm($subResource, $id = NULL) {
    if ($id === NULL) {
      $dispatcher = $this->dispatcher('GET', $this->urlPrefix.'/'.$this->entityMeta->getEntityNamePlural().'/'.$subResource);
    } else {
      $dispatcher = $this->dispatcher('GET', $this->urlPrefix.'/'.$this->entityMeta->getEntityName().'/'.$id.'/'.$subResource);
    }
    
    return new CMSFormAcceptanceTester($this->result($dispatcher, 'response'), $this);
  }
  

  /**
   * Tested eine Custom Action
   *
   * @param int|NULL $id
   * @return whatever $format is set to
   */
  public function action($id, $resourceName, $format = 'html', $method = 'GET', $data = NULL) {
    if ($id === NULL)
      $url = $this->urlPrefix.'/'.$this->entityMeta->getEntityNamePlural().'/'.$resourceName;
    else
      $url = $this->urlPrefix.'/'.$this->entityMeta->getEntityName().'/'.$id.'/'.$resourceName;
    
    $dispatcher = $this->dispatcher($method, $url);
    if ($data) {
      $dispatcher->setRequestData($this->parsePost($data));
    }
    
    return $this->result($dispatcher, $format);
  }
  
  /**
   * Test ob die Loginform angezeigt wird
   *
   * tested bereits auf präsenz von #email und #password
   * sowie /psc-cms-js/css/ui.css im header
   */
  public function loginForm($url = '/') {
    $dispatcher = $this->dispatcher('GET', $url);
    $dispatcher->removeAuthentication();
    
    $html = $this->result($dispatcher, 'html');
    
    $test = $this->testCase->getCodeTester();

    $test->css('head link[rel="stylesheet"][href="/psc-cms-js/css/ui.css"]', $html)->count(1);
    $test->css('head title')->count(1)->text($this->testCase->logicalNot($this->testCase->equalTo('')));
    $test->css('form', $html)->count(1)
      ->test('input[type="text"]#email')->count(1)->end()
      ->test('input[type="password"]#password')->count(1)->end()
      ->test('button[type="submit"]')->count(1)->hasText('Login')->end()
    ;
    
    return $html;
  }

  /**
   * The HTML of home will be returned
   */
  public function home($url = '/') {
    $dispatcher = $this->dispatcher('GET', $url);
    
    $html = $this->result($dispatcher, 'html');
    $test = $this->testCase->getCodeTester();

    return $html;
  }

  /**
   * Some Basic markup checks
   */
  public function homeDefaults($url = '/') {
    $html = $this->home($url);
    $test = $this->testCase->getCodeTester();

    $test->css('html', $html)
     ->css('head script[src="/psc-cms-js/vendor/require.js"]')->count(1)->end()
     ->css('head link[rel="stylesheet"][href="/psc-cms-js/css/ui.css"]')->count(1)->end()

     ->css('body', $html)->count(1)

       ->css('#body')->count(1)
         ->css('#content > .right > #drop-contents div.content')->count(1)->end()
       ->end()
       ->css('#header')->count(1)->end()
       ->css('#footer')->count(1)->end()
     ->end()
    ;

    return $html;
  }
  
  /**
   * Asserted das Result des Tests auf $code
   *
   * converted das json zu json wenn $type 'json' ist
   * gibt das html als string zurück wenn $type 'html' ist
   *
   * ansonsten die response
   * @return array $json|string $html|Psc\URL\Response
   */
  public function result(CMSRequestDispatcher $dispatcher, $type = 'html', $code = 200) {
    $this->lastResponse = $response = $dispatcher->dispatch();
    $this->logf("Dispatched CMS Request:\n%s", $dispatcher->getRequestDebug());
    
    $this->testCase->setHTML($response->getDecodedRaw());
    
    // versuche error meldung zu bekommen
    $msg = NULL;
    if ($response->getCode() >= 400 && $response->getHeaderField('X-Psc-Cms-Error') == 'true' && $response->getHeaderField('X-Psc-Cms-Error-Message') != NULL) {
      $msg = "\n".'Fehler auf der Seite: '.$response->getHeaderField('X-Psc-Cms-Error-Message');
    }
    $this->testCase->assertEquals($code, $response->getCode(), 'Failed asserting that Response is a '.$code.'.'.$msg);
    
    if ($type == 'json') {
      return $this->testCase->getCodeTester()->json($response->getRaw());
    } elseif ($type == 'html') {
      return $response->getDecodedRaw();
    }
    
    return $response;
  }
  
  public function dispatch($method, $url, $data, $type, $code = 200) {
    $dispatcher = $this->dispatcher($method, $url, $type);
    if ($data) {
      $dispatcher->setRequestData($this->parsePost($data));
    }
    
    return $this->result($dispatcher, $type, $code);
  }
  
  protected function parsePost($post) {
    if (is_string($post)) {
      $res = array();
      parse_str($post, $res);
      if (count($res) > 0) {
        $post = $res;
      } else {
        throw new \InvalidArgumentException('Post String konnte nicht geparsed werden: '.Code::varInfo($post));
      }
    }
    return $post;
  }
  
  /**
   * @return Psc\CMS\Code\Test\CMSRequestDispatcher
   */
  public function dispatcher($method, $url, $contentType = NULL, $publicRequest = FALSE) {
    $dispatcher = new CMSRequestDispatcher($method, $url);
    $dispatcher->setPublicRequest($publicRequest);
    
    if (isset($contentType)) {
      $dispatcher->setContentType($contentType);
    }
    
    return $dispatcher;
  }
  
  /**
   * @return Psc\URL\Response
   */
  public function getResponse() {
    return $this->lastResponse;
  }
  
  /**
   * @return Psc\CMS\EntityMeta
   */
  public function getEntityMeta($entityName = NULL) {
    if (isset($entityName)) {
      return $this->testCase->getEntityMeta($entityName);
    } else {
      return $this->entityMeta;
    }
  }
  
  public function getTestCase() {
    return $this->testCase;
  }
  
  /**
   * @param string $urlPrefix ohne / am Ende mit / am Anfang
   * @chainable
   */
  public function setUrlPrefix($urlPrefix) {
    $this->urlPrefix = $urlPrefix;
    return $this;
  }

  /**
   * @return string
   */
  public function getUrlPrefix() {
    return $this->urlPrefix;
  }
  
  /**
   * @param bool $debug
   * @chainable
   */
  public function setDebug($debug) {
    if ($debug == TRUE) 
      $this->setLogger(new \Psc\System\EchoLogger());
    else
      $this->setLogger(new \Psc\System\BufferLogger());
    $this->debug = $debug;
    return $this;
  }

  /**
   * @return bool
   */
  public function getDebug() {
    return $this->debug;
  }
}
?>