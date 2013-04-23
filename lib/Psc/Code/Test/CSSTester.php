<?php

namespace Psc\Code\Test;

use Psc\JS\jQuery;
use Psc\Code\Code;
use Psc\HTML\HTMLInterface;

class CSSTester extends \Psc\SimpleObject implements HTMLInterface {
  
  protected $testCase;
  
  protected $jQuery;
  
  protected $selector;
  protected $html;
  
  protected $parent;

  protected $msgs;
  public function __construct(Base $testCase, $selector, $html = NULL) {
    $this->testCase = $testCase;
    if ($selector instanceof jQuery) {
      $this->jQuery = $selector;
    } elseif ($html === NULL && $this->testCase instanceof HTMLTestCase) { // weil ich depp immer $this->html als 2ten parameter vergesse :)
      $this->html = $this->testCase->getHTML();
      $this->selector = $selector;
    } elseif ($html instanceof jQuery) {
      $this->jQuery = $html->find($selector);
      $this->html = NULL;
    } else {
      $this->selector = $selector;
      $this->html = $html;
    }

    $this->msgs = array(
      'hasClass'=>"Element hat die Klasse: '%s' nicht. %s"
    );
  }
  
  /**
   * Überprüft ob der CSS Selector ein Ergebnis mit genau $expected Items zurückgibt
   * 
   * @param int $expected
   */
  public function count($expected, $message = NULL) {
    $this->testCase->assertInternalType('int', $expected, 'Erster Parameter von Count muss int sein');
    $this->testCase->assertCount($expected,
                                 $this->getJQuery(),
                                 sprintf("Selector: '%s'%s",
                                         $this->getSelector(), $message ? ': '.$message : ''));
    return $this;
  }

  /**
   * Überprüft ob der CSS Selector ein Ergebnis mit mindestens $expected Items zurückgibt
   * 
   * @param int $expected
   */
  public function atLeast($expected, $message = '') {
    $this->testCase->assertInternalType('int',$expected, 'Erster Parameter von atLeast muss int sein');
    $this->testCase->assertGreaterThanOrEqual($expected, count($this->getJQuery()), $message ?: sprintf("Selector: '%s'",$this->getSelector()));
    return $this;
  }
  
  public function hasAttribute($expectedAttribute, $expectedValue = NULL) {
    $jQuery = $this->assertJQuery(__FUNCTION__);

    $this->testCase->assertTrue($jQuery->getElement()->hasAttribute($expectedAttribute), 'Element hat das Attribut: "'.$expectedAttribute.'" nicht. Context: '.\Webforge\Common\String::cut($jQuery->html(), 100,'...'));
    
    if (func_num_args() >= 2) {
      $this->testCase->assertEquals($expectedValue, $jQuery->attr($expectedAttribute), 'Wert des Attributes '.$expectedAttribute.' ist nicht identisch');
    }
    return $this;
  }
  
  public function attribute($expectedAttribute, $constraint, $msg = '') {
    $jQuery = $this->assertJQuery(__FUNCTION__);

    $this->testCase->assertTrue($jQuery->getElement()->hasAttribute($expectedAttribute), 'Element hat das Attribut: '.$expectedAttribute.' nicht. Context: '.$jQuery->html());
    
    $this->testCase->assertThat($jQuery->attr($expectedAttribute), $constraint, $msg);
    return $this;
  }

  public function hasNotAttribute($expectedAttribute) {
    $jQuery = $this->assertJQuery(__FUNCTION__);

    $this->testCase->assertFalse($jQuery->getElement()->hasAttribute($expectedAttribute), 'Element hat das Attribut: '.$expectedAttribute.' es wurde aber erwartet, dass es nicht vorhanden sein soll');
    return $this;
  }
  
  public function hasClass($expectedClass, $msg = '') {
    $this->testCase->assertTrue(
      $this->getJQuery()->hasClass($expectedClass), 
      sprintf($this->msgs[__FUNCTION__], $expectedClass, $msg)
    );
    return $this;
  }

  public function hasNotClass($expectedClass) {
    $this->testCase->assertFalse($this->getJQuery()->hasClass($expectedClass), 'Element hat die Klasse: '.$expectedClass.' obwohl es sie nicht haben soll');
    return $this;
  }

  public function hasText($expectedText, $msg = NULL) {
    $this->testCase->assertEquals(
      $expectedText, 
      $this->getJQuery()->text(), 
      sprintf("%sThe text contents of element (%s) do not match.",
        $msg ? $msg.".\n" : '',
        $this->getSelector()
      )
    );

    return $this;
  }

  public function text($constraint, $msg = '') {
    $jQuery = $this->assertJQuery(__FUNCTION__);
    
    $this->testCase->assertThat($jQuery->text(), $constraint, $msg);
    return $this;
  }
  
  public function containsText($expectedTextPart, $msg = NULL) {
    $this->testCase->assertContains(
      $expectedTextPart, 
      $this->getJQuery()->text(), 
      sprintf("%sThe text contents of element %s do not match.", 
        $msg ? $msg.".\n" : '',
        $this->getSelector()
      )
    );
    return $this;
  }

  public function hasStyle($expectedStyle, $expectedValue = NULL) {
    $jQuery = $this->assertJQuery(__FUNCTION__);
    
    $this->testCase->assertTrue($jQuery->getElement()->hasAttribute('style'), 'Element hat das Attribut: style nicht: '.$jQuery->html());
    $this->testCase->assertContains($expectedStyle, $jQuery->attr('style'));    
    
    if (func_num_args() >= 2) {
      $this->testCase->assertContains($expectedStyle.': '.$expectedValue, $jQuery->attr('style'), 'Style '.$expectedStyle.' mit Wert '.$expectedValue.' nicht gefunden');
    }
    return $this;
  }
  
  /**
   * Überprüft ob der CSS Selector mindestens einmal matched
   * 
   */
  public function exists($message = '') {
    $this->testCase->assertGreaterThan(0,count($this->getJQuery()));
    return $this;
  }
  
  /**
   * Startet einen neuen (Sub)Test mit find($selector)
   *
   * ein alias von css()
   * @discouraged
   */
  public function test($selector) {
    return $this->css($selector);
  }
  
  /**
   * Startet einen neuen (Sub)Test mit find($selector)
   */
  public function css($selector) {
    $this->assertjQuery(sprintf("css('%s')", $selector));
    $subTest = new static($this->testCase, $this->getJQuery()->find($selector));
    $subTest->setParent($this);

    return $subTest;
  }

  /**
   * Sets this css test html as context for the testcase (if its an HTMLTestCase)
   * 
   * this can be handy to reduce the debug output
   */
  public function asContext() {
    if ($this->testCase instanceof HTMLTestCase) {
      $this->testCase->setDebugContextHTML($this, 'css: '.$this->getSelector());
    }
    return $this;
  }
  
  protected function assertjQuery($function) {
    $jQuery = $this->getJQuery();
    $this->testCase->assertNotEmpty($jQuery->getElement(), 'Element kann für '.$function.' nicht überprüft werden, da der Selector 0 zurückgibt: '.$jQuery->getSelector());
    return $jQuery;
  }
  
  public function getJQuery() {
    if (!isset($this->jQuery)) {
      $this->testCase->assertNotNull($this->html, 'html ist leer. Wurde als 2ter Parameter kein HTML übergeben? Oder $this->html wurde gesetzt aber der TestCase leitet nicht HTMLTestCase ab?');
      
      $html = (string) $this->html;
      // simple heuristic, damit wir html documente korrekt asserten
      if (mb_strpos(trim($html),'<!DOCTYPE') === 0 || mb_strpos(trim($html),'<html') === 0 || mb_strpos(trim($html), '<?xml') === 0) {
        $html = \Psc\XML\Helper::doc($html);
      }
      
      $this->jQuery = new jQuery($this->selector, $html);
    }
    
    return $this->jQuery;
  }
  
  public function html() {
    return $this->getJQuery()->html();
  }
  
  public function getSelector() {
    return $this->selector ?: $this->getJQuery()->getSelector();
  }
  
  public function end() {
    return $this->parent;
  }
  
  /**
   * @param $selector 'table tr:eq(0) td' z. B: 
   * @param Closure $do erster Parameter die jquery td/th
   */
  public function readRow($selector, $expectColumnsNum, Closure $do = NULL) {
    if (!isset($do)) {
      $do = function ($td) {
        return $td->html();
      };
    }
    
    $tds = $this->getJQuery()->find($selector);
    $columns = array();
    foreach ($tds as $td) {
      $td = new jQuery($td);
      
      $columns[] = $do($td);
    }
    
    $this->testCase->assertCount($expectColumnsNum, $columns, 'Spalten der Zeile: '.$tds->getSelector().' haben nicht die richtige Anzahl '.print_r($columns,true));
    return $columns;
  }
  
  /**
   * @param  $parent
   * @chainable
   */
  public function setParent($parent) {
    $this->parent = $parent;
    return $this;
  }

  /**
   * @return 
   */
  public function getParent() {
    return $this->parent;
  }
}
