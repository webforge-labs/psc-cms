<?php

namespace Psc\CMS\Service;

use Psc\Net\ServiceRequest;

/**
 * @group class:Psc\CMS\Service\CMSService
 */
class CMSServiceTest extends \Psc\Doctrine\DatabaseTest {
  
  protected $cMSService;
  
  public function setUp() {
    $this->chainClass = 'Psc\CMS\Service\CMSService';
    $this->con = "tests";
    parent::setUp();
    $this->service = new CMSService(\Psc\PSC::getProject(), 'cms', $this->getDoctrinePackage());
  }
  
  public function testNavigationControllerRoutingFormular() {
    list($controller, $method, $params) = $this->assertNavigationRouting(
      'getFormular',
      array(),
      $this->service->routeController($this->request('GET', '/cms/navigation/default'))
    );
    
    $this->assertEquals('default', $controller->getIdent());
  }

  public function testNavigationControllerRoutingSave() {
    list($controller, $method, $params) = $this->assertNavigationRouting(
      'saveFormular',
      array(array('serialized','stuff')),
      $this->service->routeController($this->request('POST', '/cms/navigation/default', array('serialized','stuff')))
    );
    
    $this->assertEquals('default', $controller->getIdent());
  }
  
  public function testImageControllerRouting() {
    $hash = 's098sdfl324l3j45lkewj5r';
    $id = 7;
    
    // GET /cms/images/($hash|$id)
    // accept: JSON
    // => application/json Entity Image
    $this->assertImageRouting(
      'getImage',
      array($id),
      $this->service->routeController($this->request('GET', $idRQ = '/cms/images/7'))
    );
    $this->assertImageRouting(
      'getImage',
      array($hash),
      $this->service->routeController($this->request('GET', $hashRQ = '/cms/images/s098sdfl324l3j45lkewj5r'))
    );

    // GET /cms/images/($hash|$id)[/myname.png]
    // accept: image/png
    // => image/png
    $this->assertImageRouting(
      'getImage',
      array($hash, 'myname.png'),
      $this->service->routeController($this->request('GET', $hashRQ.'/myname.png'))
    );
    $this->assertImageRouting(
      'getImage',
      array($id, 'myname.png'),
      $this->service->routeController($this->request('GET', $idRQ.'/myname.png'))
    );
    
    // accept: image/png:
    // GET /cms/images/($hash|$id)/thumbnail/230/120/outbound[/myname.png]
    // GET /cms/images/($hash|$id)/thumbnail/page[/myname.png]
    // => image/png
    $this->assertImageRouting(
      'getImageVersion',
      array($id, 'thumbnail', array(230, 120, 'outbound'), 'myname.png'),
      $this->service->routeController($this->request('GET', $idRQ.'/thumbnail/230/120/outbound/myname.png'))
    );
    $this->assertImageRouting(
      'getImageVersion',
      array($hash, 'thumbnail', array(230, 120, 'outbound'), 'myname.png'),
      $this->service->routeController($this->request('GET', $hashRQ.'/thumbnail/230/120/outbound/myname.png'))
    );
    $this->assertImageRouting(
      'getImageVersion',
      array($hash, 'thumbnail', array('page'), 'myname.png'),
      $this->service->routeController($this->request('GET', $hashRQ.'/thumbnail/page/myname.png'))
    );
    $this->assertImageRouting(
      'getImageVersion',
      array($id, 'thumbnail', array('page'), 'myname.png'),
      $this->service->routeController($this->request('GET', $idRQ.'/thumbnail/page/myname.png'))
    );
    $this->assertImageRouting(
      'getImageVersion',
      array($id, 'thumbnail', array('page')),
      $this->service->routeController($this->request('GET', $idRQ.'/thumbnail/page'))
    );


    // PUT /cms/images/($hash|$id)
    // content-Type: image/png
    // body: binary data des bildes
    // => speichere unter bekanntem hash bzw id
    $this->markTestIncomplete('implement this');

    // POST /cms/images/
    // content-Type: image/png
    // body: binary data des Bildes
    // (geht das überhaupt?)
    // => füge Bild mit diesen Binärdaten ein
    $this->markTestIncomplete('implement this');
  }
  
  protected function assertImageRouting($expectedMethod, Array $expectedParams, Array $list) {
    return $this->assertRouting('Psc\CMS\Controller\ImageController', $expectedMethod, $expectedParams, $list);
  }

  protected function assertNavigationRouting($expectedMethod, Array $expectedParams, Array $list) {
    return $this->assertRouting('Psc\CMS\Controller\NavigationController', $expectedMethod, $expectedParams, $list);
  }

  protected function assertRouting($controllerClass, $expectedMethod, Array $expectedParams, Array $list) {
    list($ctrl, $method, $params) = $list;
    
    $this->assertInstanceOf($controllerClass, $ctrl);
    $this->assertEquals($expectedMethod, $method, 'Die Methode für den Controller ist falsch');
    $this->assertEquals($expectedParams, $params, 'Die Parameter für den Controller sind falsch');
    return $list;
  }
  
  protected function request($method, $url, $body = NULL) {
    if (is_string($url)) {
      $url = explode('/', ltrim($url,'/'));
    }
    return $this->doublesManager->createRequest($method, $url, $body);
  }
}
?>