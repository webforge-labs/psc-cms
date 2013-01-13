<?php

namespace Psc\UI;

/**
 * @group class:Psc\UI\UploadService
 */
class UploadServiceTest extends \Psc\Code\Test\Base {
  
  protected $uploadService;
  
  public function setUp() {
    $this->chainClass = 'Psc\UI\UploadService';
    parent::setUp();
    $this->uploadService = new UploadService('/url/api', '/url/ui');
  }
  
  public function testJooseSnippetParams() {
    $this->assertInstanceOf('Psc\JS\JooseSnippet', $snippet = $this->uploadService->getJooseSnippet());
    
    $params = $snippet->getConstructParams();
    
    // ein assertJSApi wäre auch mal cool! (liest alle requires aus Joose aus)
    $this->assertEquals('/url/ui', $params->uiUrl);
    $this->assertEquals('/url/api', $params->apiUrl);
    $this->assertObjectHasAttribute('ajaxService', $params);
    $this->assertObjectHasAttribute('exceptionProcessor', $params);
  }
}
?>