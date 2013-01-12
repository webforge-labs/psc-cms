<?php

namespace Psc\UI;

/**
 *
 *
 * eine Tabelle erstellen:
 *
 * $table = new UITable();
 *
 * $table->tr();
 * $table->td()->content = 'zeile1-spalte1';
 * $table->td()->content = 'zeile1-spalte2';
 * $table->td()->content = 'zeile1-spalte3';
 * $table->tr();
 *
 * $table->tr();
 * $table->td()->content = 'zeile2-spalte1';
 * $table->td()->content = 'zeile2-spalte2';
 * $table->td()->content = 'zeile2-spalte3';
 * $table->tr();
 *
 * usw..
 * TH und TD werden automatisch gemacht
 * 
 * create*()
 * sollten dazu benutzt werden, dem Layout der Tabelle Klassen, etc hinzuzufügen und können einfach abgeleitet werden, ohne die logik in td() und tr() kopieren zu müssen
 */
class Table extends OptionsObject implements \Psc\HTML\HTMLInterface {
  
  const FIRST      = 0x000001;
  const LAST       = 0x000002;
  
  /**
   *
   * @var HTMLTag
   */
  protected $html;
  
  
  /**
   * @var bool
   */
  protected $trOpen = FALSE;
  
  
  /**
   * Pointer auf die aktuelle Zeile
   * @var HTMLTag*
   */
  protected $tr;
  protected $firstTR;
  
  /**
   * Pointer auf die aktuelle Spalte
   * @var HTMLTag*
   */
  protected $td;
  
  /**
   * Soll in der ersten Zeile TH benutzt werdeN?
   */
  protected $header = TRUE;
  
  public function __construct() {
    $this->html = HTML::tag('table',array());
    $this->html->setOption('br.beforeContent',TRUE);
    
    $this->html->content = array();
    $this->firstTR = TRUE;
  }
  
  public function &tr() {
    if (!$this->trOpen) {
      /* Open for Columns */
      $tr = $this->createTR();
      
      $this->html->content[] =& $tr;
      
      $this->tr =& $tr;
      $this->trOpen = TRUE;
    } else {
      /* Close */
      $this->trOpen = FALSE;
      if (isset($this->td)) {
        if ($this->isHeader()) // empty row possible?
          $this->onLastTH($this->td);
        else
          $this->onLastTD($this->td);
      }
      
      if ($this->firstTR) {
        $this->onFirstTR($this->tr);
        $this->firstTR = FALSE;
      }
    }
    
    return $this->tr;
  }
  
  public function &td() {
    if (!$this->trOpen) {
      throw new \Psc\Exception('Zuerst mit tr() eine Zeile öffnen, dann Spalten hinzufügen');
    } else {
      if ($this->isHeader()) {
        $td = $this->createTH($this->tr);
      } else {
        $td = $this->createTD($this->tr);
      }
      $this->tr->content[] =& $td;
      
      $this->td =& $td;
      
      if (count($this->tr->content) == 1) {
        if ($this->isHeader())
          $this->onFirstTH($td);
        else
          $this->onFirstTD($td);
      }
    }
    return $this->td;
  }
  
  /**
   * @return HTMLTag<td>
   */
  protected function createTD(HTMLTag $tr) {
    return HTML::Tag('td');
  }
  
  /**
   * @return HTMLTag<th>
   */
  protected function createTH(HTMLTag $tr) {
    return $this->createTD($tr)->setTag('th');
  }
  
  /**
   * 
   * @return HTMLTag<tr>
   */
  protected function createTR() {
    $tr = HTML::Tag('tr',array());
    $tr->indent(2);
    $tr->setOption('br.beforeContent',TRUE);
    
    return $tr;
  }
  
  /**
   * Wird aufgerufen, wenn die Zeile abgeschlossen wird
   *
   * Kann benutzt werden um z.B. dem letzten Element der Zeile eine besondere Klasse, etc zuzuweisen
   */
  protected function onLastTD(HTMLTag $td) {}

  /**
   * Analog zu onLastTD()
   */
  protected function onFirstTD(HTMLTag $td) {}
  protected function onLastTH(HTMLTag $th) {}
  protected function onFirstTH(HTMLTag $th) {}
  protected function onFirstTR(HTMLTag $tr) {}
  

  /**
   * Gibt die beim Einfügen zuletzt eingefügte Spalte zurück
   */
  public function getTD() {
    return $this->td;
  }
  
  /**
   * @return bool
   */
  public function isHeader() {
    return count($this->html->content) == 1 && $this->header == TRUE;
  }
  
  public function getHTML() {
    return $this->html;
  }
  
  public function addClass($class) {
    $this->html->addClass($class);
  }
  
  public function setStyle($style, $value) {
    $this->html->setStyle($style,$value);
  }
  
  public function html() {
    return $this->getHTML();
  }
  
  public function __toString() { return (string) $this->html(); }
}