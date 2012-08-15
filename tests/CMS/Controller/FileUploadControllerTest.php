<?php

namespace Psc\CMS\Controller;

/**
 * @group class:Psc\CMS\Controller\FileUploadController
 */
class FileUploadControllerTest extends \Psc\Doctrine\DatabaseTestCase {
  
  protected $controller;
  
  public function setUp() {
    $this->chainClass = 'Psc\CMS\Controller\FileUploadController';
    parent::setUp();
    $this->controller = new FileUploadController($this->dc); // geht ohne parameter zu wuppen
  }
  
  public function testAcceptance() {
    $this->assertInstanceOf('Psc\CMS\UploadManager', $this->controller->getUploadManager(),'FileUploadController sollte sich einen eigenen uploadmanager bauen');
  }
  
  public function testInsertFile() {
    $cFile = $this->getCommonFile('Businessplan.docx');
    $uplFile = $this->getResponseData(
      $this->controller->insertFile($cFile, (object) array('description'=>'bp short'), FileUploadController::IF_NOT_EXISTS)
    );
    $this->assertInstanceOf('Psc\Entities\File', $uplFile);
    
    // reicht: rest im UploadManager
  }

  protected function getResponseData($response) {
    // wegen dem blöden IE nur!!! blöd!
    if ($response instanceof \Psc\Net\ServiceResponse) {
      return $response->getBody();
    } else {
      return $response;
    }
  }
}
?>