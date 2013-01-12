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
    $this->cFile = $this->getCommonFile('Businessplan.docx');
  }
  
  public function testAcceptance() {
    $this->assertInstanceOf('Psc\CMS\UploadManager', $this->controller->getUploadManager(),'FileUploadController sollte sich einen eigenen uploadmanager bauen');
  }
  
  public function testInsertFile() {
    $uplFile = $this->getResponseData(
      $this->controller->insertFile($this->cFile, (object) array('description'=>'bp short'))
    );
    $this->assertInstanceOf('Psc\Entities\File', $uplFile);
    
    // reicht: rest im UploadManager
  }
  
  public function testInsertFileStoresPublicFilenameFromUploadedFile() {
    $uploadedBinFile = new \Psc\System\UploadedFile((string) $this->cFile);
    $uploadedBinFile->setOriginalName('schnipp.pdf');
    
    $uplFile = $this->getResponseData(
      $this->controller->insertFile($uploadedBinFile, (object) array('description'=>NULL))
    );
    
    $this->assertEquals('schnipp.pdf',$uplFile->getOriginalName());
  }
  
  public function testGetFile() {
    $newUplFile = $this->getResponseData(
      $this->controller->insertFile($this->cFile, (object) array('description'=>'bp short'))
    );
    $this->dc->getEntityManager()->clear();
    
    $uplFile = $this->getResponseData($this->controller->getFile($this->cFile->getSha1()));
    $this->assertEquals($newUplFile->getIdentifier(), $uplFile->getIdentifier());
  }
  
  public function testGettedFile_canExportUrlAndFile() {
    $uplFile = $this->getResponseData(
      $this->controller->insertFile($this->cFile, (object) array('description'=>'bp short'), FileUploadController::IF_NOT_EXISTS)
    );
    
    $this->assertNotEmpty($uplFile->getURL());
    $this->assertInstanceOf('Webforge\Common\System\File', $uplFile->getFile());
  }
  
  public function testGetFiles() {
    $this->controller->insertFile($this->cFile, (object) array('description'=>'bp original'), FileUploadController::IF_NOT_EXISTS);
    $this->controller->insertFile($this->getCommonFile('Businessplan.pdf'), (object) array('description'=>'bp as pdf'), FileUploadController::IF_NOT_EXISTS);
    
    $files = $this->getResponseData(
      $this->controller->getFiles()
    );
    
    $this->assertCount(2, $files);
    $this->assertInstanceOf('Psc\Data\Exportable', $files);
    $export = $files->export();

    $this->assertCount(2, $export);
    foreach ($export as $file) {
      $this->assertObjectHasAttribute('id', $file);
      $this->assertObjectHasAttribute('url', $file);
      $this->assertObjectHasAttribute('description', $file);
      $this->assertObjectHasAttribute('originalName', $file, implode(', ', array_keys((array) $file)));
    }
    
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