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
    $this->excelController = new ExcelController(\Psc\PSC::getProject());
  }
  
  public function testFillsARowWithNullValueInBetween() {
    $this->injectFile($this->getFile('emptyColumn.xlsx'));
    
    $response = $this->excelController->convert((object) array(
      'columns'=>array() // gerade egal
    ));
    
    $converted = $response->getBody();
    $this->assertEquals(
      Array(
        array('Sound No.', 'Sound', 'Correct OIDs'),
        array('110214em125', NULL, '9999002, 9999001'), // hier kein convert in array oder so weil wir columns nicht angegeben haben
        array('2-TEST_1002', 'Finde den Affen', '9999001'),
        array('2-TEST_1003', 'Finde die Giraffe', '9999000')
      ),
      $converted
    );
  }

  public function testFullEmptyRowsAreDiscardedIfDefault() {
    $this->injectFile($this->getFile('emptyLine.xlsx'));
    
    $response = $this->excelController->convert((object) array(
      'columns'=>array() // gerade egal
    ));
    
    $converted = $response->getBody();
    $this->assertEquals(
      Array(
        array('Sound No.', 'Sound', 'Correct OIDs'),
        array(NULL, 'Klappern einer Computertastatur', '14014:BLG'), // hier kein convert in array oder so weil wir columns nicht angegeben haben
        array('091104ak258', 'Piepsen (einzelner Spatz)', '14013:BLG')
      ),
      $converted
    );
  }

  public function testFullEmptyRowsAreNotDiscardedIfEmptyAllowed() {
    $this->injectFile($this->getFile('emptyLine.xlsx'));
    
    $response = $this->excelController->convert((object) array(
      'columns'=>array() // gerade egal
    ), NULL, $removeEmpty = FALSE);
    
    $converted = $response->getBody();
    $this->assertEquals(
      Array(
        array('Sound No.', 'Sound', 'Correct OIDs'),
        array('', '', ''),
        array(NULL, 'Klappern einer Computertastatur', '14014:BLG'), // hier kein convert in array oder so weil wir columns nicht angegeben haben
        array('091104ak258', 'Piepsen (einzelner Spatz)', '14013:BLG'),
        array('', '', ''),
        array('', '', ''),
        array('', '', ''),
        array('', '', ''),
      ),
      $converted
    );
  }

  public function testWeirdoArrayConversion() {
    $this->injectFile($this->getFile('mainTable.xlsx'));
    
    $response = $this->excelController->convert(
      json_decode('{
        "columns":[
            {"name":"sound-number","label":"Sound No.","type":"string","index":0},
            {"name":"sound-content","label":"Sound","type":"string","index":1},
            {"name":"oids","label":"Correct OIDs","type":"string","index":2}
          ]
        }
      '
      ),
      NULL,
      $removeEmpty = FALSE
    );
    
    $converted = $response->getBody();
    
    $this->assertSame("13088:WEL, 13089:WEL", $converted[17][2]);
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
  
  protected function injectFile(\Webforge\Common\System\File $file) {
    $validationPackage = $this->getMock('Psc\Form\ValidationPackage', array('validateUploadedFile'));
    $validationPackage->expects($this->atLeastOnce())->method('validateUploadedFile')->will($this->returnValue($file));
    
    $this->excelController->setValidationPackage($validationPackage);
  }
}
?>