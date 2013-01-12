<?php

namespace Psc\XML;

use Psc\JS\jQuery;
use Closure;

/**
 * 
 */
class TableScraper extends \Psc\XML\ScraperBase {
  
  /**
   * @var jQuery $table
   */
  protected $table;
  
  /**
   * Soll der Header als Spaltenbezeichnungen dienen, die mit der Daten-Row kombiniert wird?
   */
  protected $useHeader;
  
  /**
   * Der Header (wenn gesetzt wird dieser benutzt, statt einen zu parsen)
   */
  protected $header;
  
  protected $rowFilters = array();
  
  public function __construct(jQuery $table) {
    $this->table = $table;
    
    parent::__construct(Array(
      'tdConverter'=>function (jQuery $td, $key, $columnName) {
        return $td->text();
      },
      'thConverter'=>function (jQuery $th, $key) {
        return $th->text();
      }
    ));
    
    $this->parseHeader();
  }
  
  /**
   * @return object .rows , .header
   */
  public function scrape() {
    extract($this->help());
    
    $rows = array();
    $header = $this->header;
    foreach ($this->table->find('tr') as $key => $tr) {
      $tr = $jq($tr);
      
      // filter?
      foreach($this->rowFilters as $rowFilter) {
        if ($rowFilter($tr, $key, $header !== NULL) === FALSE) continue(2);
      }
      
      // scrape the header (wenn er gesetzt ist parsen wir nicht, wenn er leer ist auch nicht)
      if ($header === NULL) {
        $header = array();
        foreach ($tr->find('td,th') as $key=>$th) {
          $value = $thConverter($jq($th), $key);
          $header[$key] = $value;
        }
      } else {
        // scrape a row
        $row = array();
        foreach ($tr->find('td') as $key=>$td) {
          $value = $tdConverter($jq($td), $key, array_key_exists($key, $header) ? $header[$key] : NULL); // somit geht sogar dass &$key benutzt wird
          $row[$key] = $value;
        }
        
        if ($this->useHeader) {
          $rows[] = array_combine($header, $row);
        } else {
          $rows[] = $row;
        }
      }
    }
    
    $table = (object) array('header'=>$header, 'rows'=>$rows);
    
    return $table;
  }

  /**
   * Benutzt den Header als associative Schlüssel für jede Zeile
   * 
   * @param Closure $thConverter konvertiert von jQuery($th) nach skalar function (jQuery $th, $key (0-based))
   */
  public function useHeader(Closure $thConverter = NULL) {
    $this->setHook('thConverter', $thConverter);
    $this->useHeader = TRUE;
    return $this;
  }
  
  public function setHeader(Array $header) {
    $this->header = $header;
    return $this;
  }
  
  public function dontParseHeader() {
    $this->header = array();
    return $this;
  }
  
  /**
   * Versucht als erstes den Header der Tabelle zu finden
   *
   * dieser wird dann nicht zu den rows hinzugefügt
   * diese Einstellung ist unabhängig von useHeader (die auch false sein kann)
   */
  public function parseHeader(Closure $thConverter = NULL) {
    $this->setHook('thConverter', $thConverter);
    $this->header = NULL;
    return $this;
  }
  
  /**
   * function (jQuery $td, $key, $columnName) {
   *
   * wenn useHeader gesetzt ist, ist $columnName der Name der Spalte
   *
   * die DefaultImplementierung ist return $td->text();
   */
  public function tdConverter(Closure $tdConverter) {
    $this->setHook('tdConverter', $tdConverter);
    return $this;
  }

  public function thConverter(Closure $thConverter) {
    $this->setHook('thConverter', $thConverter);
    return $this;
  }
  
  /**
   * Closure function (jQuery $tr, bool $headerFound) und muss dann bool zurückgeben
   *
   * ist headerFound FALSE wurde der header noch nicht geparsed
   * gibt filter FALSE zurück wird die row übersprungen
   */
  public function rowFilter(\Closure $filter) {
    $this->rowFilters[] = $filter;
    return $this;
  }
}
?>