<?php

namespace Psc\PHPExcel;

class SimpleImporterTest extends \Webforge\Code\Test\Base {
  
  public function setUp() {
    $this->chainClass = 'Psc\\PHPExcel\\SimpleImporter';
    parent::setUp();
    
    $this->matrix1File = $this->getFile('excels/matrix1.xlsx');

    $this->matrix1['VariableName'] = Array(
      2 => array(
        'Names'=>'mode',
        'Type'=>'counter',
        'Initialize Value'=>1,
        'Comment'=>NULL
      ),
      3 => array(
        'Names'=>'tmp',
        'Type'=>'counter',
        'Initialize Value'=>0, // this works because in matrix1 the column is formatted as INTEGER (thats very important)
        'Comment'=>NULL
      )
    );
  }
  
  public function testImporterCanRead_AllRows_FromSheet0ByIndex_WithFirstLineAsColumns() {
    $sheets = SimpleImporter::createFromFile($this->matrix1File)
      ->useFirstLineAsColumns()
      ->selectSheetByIndex(0)
      ->findAll();
      
    $this->assertContainsOnlyInstancesOf('Psc\PHPExcel\Sheet', $sheets);
    $this->assertCount(1, $sheets, 'one sheet should be returned, because one is selected');

    $this->assertRowsEqual(
      $this->matrix1['VariableName'],
      current($sheets)
    );
  }

  public function testImporterCanRead_AllRows_FromSheetByName_WithFirstLineAsColumns() {
    $sheets = SimpleImporter::createFromFile($this->matrix1File)
      ->useFirstLineAsColumns()
      ->selectSheet('VariableName')
      ->findAll();
      
    $this->assertContainsOnlyInstancesOf('Psc\PHPExcel\Sheet', $sheets);
    $this->assertCount(1, $sheets, 'one sheet should be returned, because one is selected');

    $this->assertRowsEqual(
      $this->matrix1['VariableName'],
      current($sheets)
    );
  }
  
  protected function assertRowsEqual(array $expectedRowData, Sheet $sheet) {
    $actualRowData = array();
    foreach ($sheet->getRows() as $rowIndex => $row) {
      foreach ($row as $column => $value) {
        $actualRowData[$rowIndex][$column] = $value;
      }
    }
    
    return $this->assertEquals($expectedRowData, $actualRowData, 'Sheet Rows from '.$sheet);
  }
}
?>