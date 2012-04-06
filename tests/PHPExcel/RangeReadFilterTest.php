<?php

namespace Psc\PHPExcel;

class RangeReadFilterTest extends \Psc\Code\Test\Base {
  
  protected $filter;
  
  public function setUp() {
    $this->chainClass = 'Psc\PHPExcel\RangeReadFilter';
    parent::setUp();
    $this->filter = new RangeReadFilter(array('AA','CA'), array(6,66));
  }
  
//  public function testRangeDebug() {
//    var_dump($this->filter->getRange());
//  }
  
  /**
   * @dataProvider provideRange
   */
  public function testRange($return, $column, $row) {
    $this->assertEquals($return, $this->filter->readCell($column, $row));
  }
  
  public static function provideRange() {
    return array(
      // untere grenze(n)
      array(FALSE, 'AA', 5),
      array(FALSE, 'Z', 10),
      array(TRUE, 'AA', 6),
      
      // midde
      array(TRUE, 'AF', 10),
      array(TRUE, 'BG', 10),
      array(TRUE, 'BZ', 20),

      // obere grenze(n)
      array(FALSE, 'AF', 67),
      array(TRUE, 'CA', 66),
      array(TRUE, 'BZ', 10),
      array(FALSE, 'CA', 67),
      array(FALSE, 'CB', 66),
    );
  }
}
?>