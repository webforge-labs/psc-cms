<?php

namespace Psc\CMS\Service;

use Psc\Net\ServiceRequest;

/**
 * @group class:Psc\CMS\Service\CMSService
 */
class CMSServiceTest extends \Psc\Code\Test\Base {
  
  public function setUp() {
    $this->chainClass = 'Psc\CMS\Service\CMSService';
    $this->con = "tests";
    parent::setUp();
    
    $this->dc = $this->getModule('Doctrine')->createDoctrinePackage();
    $this->service = new CMSService($this->getProject(), 'cms', $this->dc);
  }
  
  public function testFileControllerRoutingGetFiles() {
    list($controller, $method, $params) = $this->assertFileUploadRouting(
      'getFiles',
      array(array(), array()),
      $this->service->routeController($this->request('GET', '/cms/uploads'))
    );
  }

  public function testFileControllerRoutingGetFilesWithOrderByMapping() {
    list($controller, $method, $params) = $this->assertFileUploadRouting(
      'getFiles',
      array(array(), array('originalName'=>'ASC')),
      $this->service->routeController($this->request('GET', '/cms/uploads')->setQuery(array('orderby'=>array('name'=>'ASC'))))
    );
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

    // POST /cms/images/
    // hardcoded in: UploadedImage.js
    // content-Type: multipart/form-data
    // body: bodyAsJSON parameter für das bild (to be defined)
    // uploadFile: die uploaded file des bildes mit binary data
    $file = \Webforge\Common\System\File::createTemporary();
    $file->writeContents('ÿØÿà�JFIF��H�H��ÿáËExif��MM�*������������������¦��������������º�������Â');
    
    list ($controller, $method, $params) = $this->assertImageRouting(
      'insertImageFile',
      array($file, (object) array('types'=>array('jpg','png','gif'))),
      $this->service->routeController($this->request('POST', '/cms/images')
                                        ->setBody((object) array('types'=>array('jpg','png','gif')))
                                        ->setFiles(array('uploadFile'=>$file))
                                      )
    );
    
    list($imageFile, $body) = $params;
    $this->assertTrue($imageFile->exists());
  }

  public function testImageRoutingPutAndPost() {
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

  public function testExcelControllerConvertRoutingWithoutAnyBody() {
    list($ctrl, $method, $params) = $this->assertExcelRouting(
      'convert',
      array(new \stdClass),
      $this->service->routeController($this->request('POST', '/cms/excel/convert', $body = ''))
    );
  }
  
  protected function assertImageRouting($expectedMethod, Array $expectedParams = NULL, Array $list) {
    return $this->assertRouting('Psc\CMS\Controller\ImageController', $expectedMethod, $expectedParams, $list);
  }

  protected function assertExcelRouting($expectedMethod, Array $expectedParams = NULL, Array $list) {
    return $this->assertRouting('Psc\CMS\Controller\ExcelController', $expectedMethod, $expectedParams, $list);
  }

  protected function assertFileUploadRouting($expectedMethod, Array $expectedParams = NULL, Array $list) {
    return $this->assertRouting('Psc\CMS\Controller\FileUploadController', $expectedMethod, $expectedParams, $list);
  }

  protected function assertRouting($controllerClass, $expectedMethod, Array $expectedParams = NULL, Array $list) {
    list($ctrl, $method, $params) = $list;
    
    $this->assertInstanceOf($controllerClass, $ctrl);
    $this->assertEquals($expectedMethod, $method, 'Die Methode für den Controller ist falsch');
    if (is_array($expectedParams))
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