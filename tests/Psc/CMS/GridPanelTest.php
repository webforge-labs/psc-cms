<?php

namespace Psc\CMS;

use Psc\JS\jQuery;

/**
 * @group class:Psc\CMS\GridPanel
 */
class GridPanelTest extends \Psc\Code\Test\HTMLTestCase {
  
  protected $gridPanel;
  
  public function setUp() {
    $this->chainClass = 'Psc\CMS\GridPanel';
    parent::setUp();
    $this->gridPanel = new GridPanel('A nice Looking Panel for items in a Table', $this->getTranslationContainer());
    $this->gridPanel->createColumn('spalte1', $this->getType('String'));
    $this->gridPanel->createColumn('spalte2', $this->getType('Integer'));
    $this->gridPanel->createColumn('spalte3', $this->getType('Integer'), 'Spalte 3');
  }
  
  public function testInterface() {
    $this->assertInstanceof('Psc\HTML\HTMLInterface', $this->gridPanel);
  }
  
  public function testHeaderHTML() {
    $this->html = $this->gridPanel->html();
    $this->assertInstanceof('Psc\HTML\HTMLInterface', $this->html);
    
    // header sind gesetzt
    $ths = $this->test->css('table tr th', $this->html)
      ->count(3)
      ->getJQuery();
    
    // header sind richtige reihenfolge
    $this->assertEquals('spalte1',$ths->eq(0)->text());
    $this->assertEquals('spalte2',$ths->eq(1)->text());
    $this->assertEquals('Spalte 3',$ths->eq(2)->text());
  }
  
  public function testRowHTML() {
    $this->gridPanel->addRow(array('row1:col1', 'row1:col2', 'row1:col3'));
    $this->gridPanel->addRow(array('row2:col1', 'row2:col2', 'row2:col3'));
    $this->gridPanel->addRow(array('row3:col1', 'row3:col2', 'row3:col3'));
    
    $trs = $this->test->css('table tr', $this->html = $this->gridPanel->html())->getJQuery();
    
    foreach ($trs as $row=>$tr) {
      if ($row === 0) continue; // skip header
      $tr = new jQuery($tr);
      $col = 1;
      foreach ($tr->find('td') as $td) {
        $td = new jQuery($td);
        // everything i right place?
        $this->assertEquals(sprintf('row%d:col%d', $row, $col), $td->text());
        $col++;
      }
      $row++;
    }
  }
  
  /**
   * @expectedException InvalidArgumentException
   */
  public function testAddRowGimbelException() {
    $this->gridPanel->addRow(array('im not implemented'=>'value'));
  }

  /**
   * @expectedException InvalidArgumentException
   */
  public function testAddRowToManyColumnsException() {
    $this->gridPanel->addRow(array('size','to','big','for','array'));
  }

  /**
   * @expectedException InvalidArgumentException
   */
  public function testAddRowToLessColumnsException() {
    $this->gridPanel->addRow(array('sizeto','small'));
  }
}
