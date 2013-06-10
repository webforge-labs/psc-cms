<?php

namespace Psc\CMS;

use Psc\UI\GridTable;
use Psc\UI\Group;
use Psc\UI\FormPanel;
use Psc\UI\PanelButtons;
use Psc\Data\Type\Type;
use Webforge\Common\ArrayUtil AS A;
use InvalidArgumentException;
use Psc\Data\Set;
use Psc\UI\DataScreener;
use Psc\HTML\HTML;
use Closure;
use Psc\CMS\Translation\Container as TranslationContainer;

/**
 * 
 */
class GridPanel extends \Psc\HTML\JooseBase {
  
  const EMPTY_LABEL = '';
  
  /**
   * @var array nach name geschlüsselt
   */
  protected $columns;
  
  /**
   * @var Psc\UI\GridTable
   */
  protected $grid;
  
  /**
   * @var Psc\CMS\Form
   */
  protected $form;
  
  /**
   * @var string
   */
  protected $label;
  
  /**
   * @var Psc\UI\DataScreener
   */
  protected $dataScreener;
  
  /**
   * @var bool
   */
  protected $sortable;

  /**
   * @var string
   */
  protected $sortableName = 'sort';
  
  /**
   * @var Psc\CMS\RequestMeta
   */
  protected $formRequestMeta;

  /**
   * @var Psc\CMS\Translation\Container
   */
  protected $translationContainer;
  
  public function __construct($label, TranslationContainer $translationContainer, PanelButtons $panelButtons = NULL, DataScreener $dataScreener = NULL, $sortable = false, RequestMeta $formRequestMeta = NULL) {
    $this->translationContainer = $translationContainer;
    $this->columns = array();
    $this->dataScreener = $dataScreener ?: new DataScreener();
    $this->label = $label;
    $this->setSortable($sortable);
    $this->setFormRequestMeta($formRequestMeta ?: new RequestMeta('POST', ''));
    
    $this->panelButtons = $panelButtons; // getter inject
    parent::__construct('Psc.UI.GridPanel');
  }
  
  /**
   * Erstellt eine Column und fügt diese direkt hinzu
   *
   * @return GridPanelColumn
   */
  public function createColumn($name, Type $type, $label = NULL, Array $additionalClasses = array(), Closure $toHTMLCallback = NULL) {
    if (!isset($label)) $label = $name;
    $column = new GridPanelColumn($name, $type, $label, $additionalClasses);
    
    if ($toHTMLCallback) {
      $column->setConverter($toHTMLCallback);
    } else {      
      $column->setConverter($this->getDefaultConverter($column));
    }
    
    $this->addColumn($column);
    
    return $column;
  }
  
  /**
   * @return Closure
   */
  public function getDefaultConverter(GridPanelColumn $column) {
    $dataScreener = $this->dataScreener;
    $type = $column->getType();
    return function ($value, $column) use ($dataScreener, $type) {
      return $dataScreener->toString($value, $type);
    };
  }
  
  /**
   * @param mixed[] $columns die Spalten in der richtigen Reihenfolge
   */
  public function addRow(Array $row) {
    $this->init();
    
    $names = array_keys($this->columns);
    
    if (!A::isNumeric($row)) {
      throw new InvalidArgumentException('Bis jetzt nur numerische Arrays. Die Reihenfolge ist: '.implode(', ',$names));
    }
    if (count($row) !== count($names)) {
      throw new InvalidArgumentException('Spalten für addRow haben nicht die richtige Anzahl. Die Reihenfolge ist: '.implode(', ',$names));
    }
    
    $grid = $this->getGrid();
    
    $grid->tr();
    foreach($row as $index => $value) {
      $column = $this->getColumn($names[$index]);
      
      // validate wäre schön nach type?
      $td = $grid->td()->setContent($value)
        ->addClass($this->classify($column->getName())) // name als klasse
        ->addClass($column->getClasses()) // zusätzliche klassen aus der column definition
      ;
    }
    $grid->tr();
    return $this;
  }
  
  protected function doInit() {
    $this->grid = $grid = new GridTable();
    
    // header
    $grid->tr();
    foreach ($this->columns as $name => $column) {
      $grid->td()->setContent($column->getLabel())
        ->addClass($this->classify($name))
        ->addClass($column->getClasses()) // zusätzliche klassen aus der column definition
      ;
    }
    $grid->tr();
    
    $panel = new FormPanel($this->label, $this->translationContainer, $this->form = new Form(NULL, $this->getFormRequestMeta()), $this->getPanelButtons());
    $panel->setWidth('100%');
    $panel->addContent($this->grid);

    $this->html = $panel->html();
    $this->constructParams['grid'] = self::SELF_SELECTOR;
    $this->constructParams['columns'] = array_keys($this->columns);
    $this->constructParams['sortable'] = $this->sortable;
    $this->constructParams['sortableName'] = $this->sortableName;
    $this->constructParams['eventManager'] = new \Psc\JS\Code('main.getEventManager()');
    
    /* set Parameters for joose */
    $this->registerToMain('GridPanel');
    $this->autoLoad();
  }
  
  public function getForm() {
    return $this->form;
  }
  
  public function getGrid() {
    return $this->grid;
  }
  
  /**
   * Gibt den Wert als HTML-Klasse zurück
   */
  protected function classify($value) {
    return HTML::string2class($value);
  }
  
  /**
   * @param Psc\UI\PanelButtons $panelButtons
   * @chainable
   */
  public function setPanelButtons(PanelButtons $panelButtons) {
    $this->panelButtons = $panelButtons;
    return $this;
  }
  
  /**
   * @return Psc\UI\PanelButtons
   */
  public function getPanelButtons() {
    if (!isset($this->panelButtons)) {
      $this->initPanelButtons();
    }
    return $this->panelButtons;
  }
  
  protected function initPanelButtons() {
    $this->panelButtons = new PanelButtons(array('reload'), $this->translationContainer);
    return $this;
  }
  
  /**
   * @param array $columns
   * @chainable
   */
  public function setColumns(Array $columns) {
    $this->columns = $columns;
    return $this;
  }
  
  /**
   * @return GridPanelColumn[]
   */
  public function getColumns() {
    return $this->columns;
  }
  
  public function addColumn(GridPanelColumn $column) {
    $this->columns[$column->getName()] = $column;
    return $this;
  }
  
  public function removeColumn(GridPanelColumn $column) {
    unset($this->columns[$column->getName()]);
  }
  
  /**
   * @return GridPanelColumn
   */
  public function getColumn($name) {
    return $this->columns[$name];
  }
  
  /**
   * @param bool $sortable
   */
  public function setSortable($sortable) {
    $this->sortable = $sortable;
    return $this;
  }
  
  /**
   * @return bool
   */
  public function getSortable() {
    return $this->sortable;
  }
  
  /**
   * @param Psc\CMS\RequestMeta $formRequestMeta
   */
  public function setFormRequestMeta(RequestMeta $formRequestMeta) {
    if ($this->form) {
      $this->form->setRequestMeta($formRequestMeta);
    }
    $this->formRequestMeta = $formRequestMeta;
    return $this;
  }
  
  /**
   * @return Psc\CMS\RequestMeta
   */
  public function getFormRequestMeta() {
    return $this->formRequestMeta;
  }
  
  /**
   * @param string $sortableName
   */
  public function setSortableName($sortableName) {
    $this->sortableName = $sortableName;
    return $this;
  }
  
  /**
   * @return string
   */
  public function getSortableName() {
    return $this->sortableName;
  }
}
?>