<?php

class FluidGrid_Table extends FluidGrid_Model {

  /**
   * @var array
   */
  protected $headRows = array();
  /**
   * @var array
   */
  protected $bodyRows = array();
  /**
   * @var array
   */
  protected $footRows = array();

  /**
   * Zeilenklassen Switcher
   * @var string
   */
  protected $oddEven;

  /**
   * 
   * @var FluidGrid_Model
   */
  protected $heading;

  /**
   * Die Anzahl der Spalten der Tabelle
   * @var int
   */  
  protected $width;

  /**
   * 
   * <code>
   * FluidGrid_Grid::factory(FluidGrid_Table::factory(
   * Array(
   *   'heading'=>'Table Heading',
   *   'head'=>Array(array('Column 1','Column 2','Column 3')),
   *   'body'=>Array(
   *     array('Lorem ipsum','Dolor sit','$ 125.00'),
   *     array('Dolor sit','Nostrud exerci','$ 75.00'),
   *     array('Nostrud exerci','Lorem ipsum','$200.00'),
   *     array('Dolor sit','Nostrud exerci','$ 175.00'),
   *     )
   *    )
   * ));
   * </code>
   * @param array
   */
  public function __construct(Array $content = NULL) {
    foreach ($content as $type => $rows) {
      if (is_numeric($type)) {
        $type = 'body';
        $this->addRow($rows);
      } else {
     
        if ($type == 'heading')
          $this->setHeading($rows);
        else {
          foreach ($rows as $row) {
            $this->addRow($row,$type);
          }
        }
      }
    }
  }

  public static function factory($content = NULL) {
    return new FluidGrid_Table($content);
  }

  public function getContent() {
    $n = $this->lb();
    $html = $this->indent().'<table>'.$n;

    $this->level++;

    if (!empty($this->headRows)) {
      $html .= $this->indent().'<thead>'.$n;
    
      if (isset($this->heading)) {
        $this->level++;
        $html .= $this->indent().'<tr>'.$n;
        $this->level++;
        $html .= $this->indent().'<th class="table-head" colspan="'.$this->getWidth().'">'.$this->heading.'</th>'.$n;
        $this->level--;
        $html .= $this->indent().'</tr>'.$n;
        $this->level--;
      }
      foreach ($this->headRows as $row) {
        $html .= $this->htmlRow($row, 'head');
      }
      $html .= $this->indent().'</thead>'.$n;
    }

    if (!empty($this->footRows)) {
      $html .= $this->indent().'<tfoot>'.$n;
      foreach ($this->footRows as $row) {
        $html .= $this->htmlRow($row);
      }
      $html .= $this->indent().'</tfoot>'.$n;
    }
    
    if (!empty($this->bodyRows)) {
      $html .= $this->indent().'<tbody>'.$n;
      foreach ($this->bodyRows as $row) {
        $html .= $this->htmlRow($row);
      }
      $html .= $this->indent().'</tbody>'.$n;
    }

    $this->level--;
    $html .= $this->indent().'</table>'.$n;

    return $html;
  }

  protected function htmlRow(Array $row, $type = 'body') {
    $this->oddEven = ($this->oddEven == 'even') ? 'odd' : 'even';
    
    $html = NULL;
    $n = $this->lb();
    
    $this->level++; // tr einrücken
    
    if ($type == 'head')
      $html .= $this->indent().'<tr>'.$n; // im head kein odd even als klasse  
    else
      $html .= $this->indent().'<tr class="'.$this->oddEven.'">'.$n;

    foreach ($row as $column) {
      $htmlCol = $this->htmlColumn($column, $type == 'head' ? 'th' : 'td'); // vielleicht wäre es noch schöner beim hinzufügen schon das array in ein objekt umzuwandeln und dies hier nicht zu tun
      $html .= (string) $htmlCol;
    }

    $html .= $this->indent().'</tr>'.$n;
    $this->level--;

    return $html;
  }

  /**
   * 
   * @return FluidGrid_HTMLElement
   */
  protected function htmlColumn($column, $tag = 'td') {
    $attributes = array();
    if (is_array($column)) {
      foreach ($column as $key=>$item) {
        if (is_numeric($key))
          $content = $item;
        else
          $attributes[$key] = $item;
      }
    } else {
      $content = $column;
    }
    
    if ($content instanceof FluidGrid_HTMLElement && ($content->getTag() == 'th' || $content->getTag() == 'td')) {
      $htmlCol = $content;
    } elseif ($content instanceof FluidGrid_Model) {
      $htmlCol->setContent($content);
    } else {
      $htmlCol = new FluidGrid_HTMLElement($tag);
      $htmlCol->setContent(new FluidGrid_String($content));
    }
    $htmlCol->setAttributes($attributes);
    $htmlCol->setLevel($this->level+1);
    
    return $htmlCol;
  }


  public function setHeading($string) {
    if (!$string instanceof FluidGrid_Model)
      $string = new FluidGrid_String($string);

    $this->heading = $string;
    return $this;
  }
  /**
   * 
   * $table->addRow(Array('column1','column2','column3'));
   */
  public function addRow(Array $row, $type = 'body') {
    $this->width = max(count($row),$this->width);
    
    switch ($type) {
    case 'body':
    default:
      $this->bodyRows[] = $row;
      break;
    case 'head':
      $this->headRows[] = $row;
      break;
    case 'foot':
      $this->footRows[] = $row;
      break;
    }
    return $this;
  }
  /**
   * 
   * @see addColumn()
   */
  public function openRow() {
    $this->openRow = array();
    return $this;
  }

  /**
   * 
   * $table->openRow();
   * $table->addColumn('column1');
   * $table->addColumn('column2');
   * $table->addColumn('column3');
   * $table->closeRow();
   */
  public function addColumn($column) {
    if (!isset($this->openRow))
      throw new Exception('Bitte zuerst eine Zeile mit openRow() öffnen.');

    $this->openRow[] = $column;
  }
  /**
   * 
   * @see closeRow();
   */
  public function closeRow() {
    if (!is_array($this->openRow))
      throw new Exception('Es ist keine Zeile mit openRow() geöffnet worden und mit addColumn() wurden keine Spalten hinzugefügt');
    $this->addRow($this->openRow);
    $this->openRow = NULL;
    return $this;
  }
     
}

?>