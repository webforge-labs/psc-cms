<?php

namespace Psc\CMS\Controller;

/**
 * @group class:Psc\CMS\Controller\ExcelController
 */
class ExcelControllerTest extends \Psc\Code\Test\Base {
  
  protected $excelController;
  
  public function setUp() {
    $this->chainClass = 'Psc\CMS\Controller\ExcelController';
    parent::setUp();
    //$this->excelController = new ExcelController();
  }
  
  public function testCreate() {
    $this->markTestIncomplete('@TODO siehe tiptoi');
  }
  
  public function testCreateAcceptance_usesFileNameAsAttachmentName() { // responseConverter
    $this->markTestIncomplete('@TODO siehe tiptoi');
  }

  public function testCreate_createsArrayASJSON() {
    $this->markTestIncomplete('@TODO siehe tiptoi');
  }

  public function testConvert_returns2DimArrayWith0basedIndizes() {
    $this->markTestIncomplete('@TODO siehe tiptoi');
  }
  
  public function testConvert_readsColumnsFromBodyAndConvertsJSONDataBackToArray() {
    $this->markTestIncomplete('@TODO siehe tiptoi');
  }
}
?>