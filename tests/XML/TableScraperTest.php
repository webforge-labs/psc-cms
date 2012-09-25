<?php

namespace Psc\XML;

/**
 * @group class:Psc\XML\TableScraper
 */
class TableScraperTest extends \Psc\Code\Test\Base {
  
  protected $scraper;
  
  public function setUp() {
    $this->chainClass = 'Psc\XML\TableScraper';
    parent::setUp();
    
    $this->scraper = new Scraper();
    
    // info:  das ist ein table wie auf http://www.jqwidgets.com/jquery-widgets-demo/demos/jqxtabs/index.htm?%28classic%29#demos/jqxtabs/tabswithimages.htm
    // halt der specification-table
    $this->setTableHTML();
  }
  
  public function testAcceptanceScraping() {
    $rows = array();
    $row = function ($name, $type, $default) use (&$rows) {
      $rows[] = array('Name'=>$name, 'Type'=>$type, 'Default'=>$default);
    };

    $table = $this->scraper
      ->table($this->tableHTML)
      ->useHeader()
      ->rowFilter(function ($row, $headerFound) {
        return !$headerFound || $row->find('td.documentation-option-type-click')->length > 0;
      })
      ->scrape();
    
    $this->assertEquals(array('Name','Type','Default'), $table->header);
    
    $row('width', 'Number/String', 'auto');
    $row('disabled', 'Boolean', 'false');
    $row('scrollAnimationDuration', 'Number', '250');
    
    $this->assertEquals($rows, $table->rows, print_r($table->rows, true));
  }
  
  public function testWithoutHeaderScraping() {
    $table = $this->scraper
      ->table($this->tableHTML)
      ->parseHeader()
      ->rowFilter(function ($row, $headerFound) {
          return !$headerFound || $row->find('td.documentation-option-type-click')->length > 0;
      })
      ->scrape();
      
    $this->assertEquals(array(
        array('width','Number/String','auto'),
        array('disabled','Boolean','false'),
        array('scrollAnimationDuration','Number','250'),
      ),
      $table->rows,
      print_r($table->rows, true)
    );
  }


  public function setTableHTML() {
    $this->tableHTML = <<<'HTML'
<table class="documentation-table">
  <tbody>
    <tr>
      <th>Name</th>

      <th>Type</th>

      <th>Default</th>
    </tr>

    <tr>
      <td class="documentation-option-type-click"><span id="property-name-disabled">width</span></td>

      <td><span>Number/String</span></td>

      <td>auto</td>
    </tr>

    <tr>
      <td style="width: 100%" colspan="3">
        <div style="display: none;" class="documentation-option-description property-content">
          <p>Gets or sets widget's width.</p>

          <h4>Code examples</h4>

          <p>Initialize a jqxTabs with the <code>width</code> property specified.</p>
          <pre>
<code>$('#jqxTabs').jqxTabs({width:"200px"});</code>
</pre>
        </div>
      </td>
    </tr>

    <tr>
      <td class="documentation-option-type-click"><span id="Span2">disabled</span></td>

      <td><span>Boolean</span></td>

      <td>false</td>
    </tr>

    <tr>
      <td style="width: 100%" colspan="3">
        <div style="display: none;" class="documentation-option-description property-content">
          <p>Enables or disables the jqxTabs widget.</p>

          <h4>Code examples</h4>

          <p>Initialize a jqxTabs with the <code>disabled</code> property specified.</p>
          <pre>
<code>$('#jqxTabs').jqxTabs({ disabled:true }); </code>
</pre>
        </div>
      </td>
    </tr>

    <tr>
      <td class="documentation-option-type-click"><span id="Span3">scrollAnimationDuration</span></td>

      <td><span>Number</span></td>

      <td>250</td>
    </tr>

    <tr>
      <td style="width: 100%" colspan="3">
        <div style="display: none;" class="documentation-option-description property-content">
          <p>Gets or sets the duration of the scroll animation.</p>

          <h4>Code examples</h4>

          <p>Initialize a jqxTabs with the <code>scrollAnimationDuration</code> property specified.</p>
          <pre>
<code>$('#jqxTabs').jqxTabs({ scrollAnimationDuration: 200 }); </code>
</pre>
        </div>
      </td>
    </tr>
  </tbody>
</table>

HTML;
  }
}
?>