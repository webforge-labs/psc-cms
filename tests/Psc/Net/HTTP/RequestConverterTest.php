<?php

namespace Psc\Net\HTTP;

/**
 * @group class:Psc\Net\HTTP\RequestConverter
 */
class RequestConverterTest extends \Psc\Code\Test\Base {
  
  protected $requestConverter;
  
  public function setUp() {
    $this->chainClass = 'Psc\Net\HTTP\RequestConverter';
    parent::setUp();
    $this->requestConverter = new RequestConverter();
  }
  
  public function testConverterParsesParts() {
    $request = new Request(Request::POST, '/cms/images');
    
    $serviceRequest = $this->requestConverter->fromHTTPRequest($request);
    $this->assertInstanceOf('Psc\Net\ServiceRequest', $serviceRequest);
    $this->assertEquals(array('cms','images'), $serviceRequest->getParts());
  }
  
  public function testMultiPartHTTPRequetsDataConversionIntoFiles() {
    
    $request = new Request(Request::POST, '/cms/images');
    $request
      ->setHeaderField('Content-Type', 'multipart/form-data; boundary=-----------------------------41184676334')
      ->setBody(array('bodyAsJSON'=>'{"types":["jpg","png","gif"]}'))
      ->setFiles(array('uploadFile'=>\Webforge\Common\System\File::createTemporary())); // das würde der HTTPRequest schona lles bei infer() checken
      
    $serviceRequest = $this->requestConverter->fromHTTPRequest($request);
    
    $this->assertTrue($serviceRequest->hasFiles());
    $this->assertInternalType('array', $files = $serviceRequest->getFiles());
    $this->assertCount(1, $files);
    $this->assertInstanceOf('Webforge\Common\System\File', $files['uploadFile']);
    
    $this->assertEquals((object) array('types'=>array('jpg','png','gif')), $serviceRequest->getBody());
  }
  
  public function testRevisionMetaHeaderIsConvertedToMeta() {
    $request = new Request(Request::PUT, '/entities/article/7');
    $request->setHeaderField('X-Psc-Cms-Revision', 'preview-1127');
    
    $serviceRequest = $this->requestConverter->fromHTTPRequest($request);
    
    $this->assertEquals(
      'preview-1127',
      $serviceRequest->getMeta('revision')
    );
  }
}
?>