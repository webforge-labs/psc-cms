<?php

namespace Psc\UI;

use Psc\CMS\AutoCompleteRequestMeta;
use Psc\CMS\Item\Exporter;
use Psc\CMS\EntityMeta;
use Psc\Code\Code;

class ComboBox2 extends \Psc\HTML\JooseBase implements \Psc\JS\JooseSnippetWidget {
  
  /**
   * Wird angezeigt wenn das Textfeld leer ist
   *
   * @var string
   */
  protected $initialText = 'Begriff hier eingeben, um die Auswahl einzuschränken.';
  
  /**
   * @var Psc\CMS\AutoCompleteRequestMeta
   */
  protected $acRequestMeta;

  /**
   * @var Collection
   */
  protected $avaibleItems;
  
  
  /**
   * @var object<EntityMeta->getClass()>
   */
  protected $selected;
  
  /**
   * Die EntityMeta für die avaibleItems
   * 
   * @var Psc\CMS\EntityMeta
   */
  protected $entityMeta;
  
  /**
   * @var string
   */
  protected $name;
  
  /**
   * @var Psc\CMS\Item\Exporter
   */
  protected $exporter;
  
  /**
   * @var bool
   */
  protected $selectMode = FALSE;
  
  public function __construct($name, $avaibleItems, EntityMeta $avaibleItemEntityMeta = NULL, Exporter $exporter = NULL) {
    $this->name = $name;
    $this->exporter = $exporter ?: new Exporter();
    $this->entityMeta = $avaibleItemEntityMeta;
    
    if ($avaibleItems instanceof AutoCompleteRequestMeta) {
      $this->setAutoCompleteRequestMeta($avaibleItems);
    } else {
      $this->setAvaibleItems($avaibleItems);
    }
    
    parent::__construct('Psc.UI.ComboBox');
  }
  
  protected function doInit() {
    $this->html =
      HTML::tag(
        'input',
        NULL,
        array(
          'type'=>'text',
          'name'=>$this->convertHTMLName(array_merge(array('disabled'), (array) $this->name))
        )
      )->addClass('\Psc\combo-box')
    ;
    
    $this->autoLoadJoose(
      $this->getJooseSnippet()
    );
  }
    
  public function getJooseSnippet() {
    $constructParams['widget'] = $this->widgetSelector();
    $constructParams['initialText'] = $this->getInitialText();
    $constructParams['selectMode'] = $this->selectMode;
    $constructParams['name'] = $this->convertHTMLName($this->name);
    
    if (isset($this->selected)) {
      $constructParams['selected'] = $this->exportItem($this->selected);
    }
    
    // dynamic mode mit Ajax
    if (isset($this->acRequestMeta)) {
      $constructParams['autoComplete'] =
        $this->createJooseSnippet(
          'Psc.UI.AutoComplete', array(
            'url'=>$this->acRequestMeta->getUrl(),
            'method'=>$this->acRequestMeta->getMethod(),
            'widget'=>$this->widgetSelector(),
            'maxResults'=>$this->acRequestMeta->getMaxResults(),
            'delay'=>$this->acRequestMeta->getDelay(),
            'minLength'=>$this->acRequestMeta->getMinLength() // wird im moment eh überschrieben
          )
        )
      ;
        
    } else {
      // static mode ohne Ajax mit exportierten Items
      $constructParams['autoComplete'] =
        $this->createJooseSnippet(
          'Psc.UI.AutoComplete', array(
            'avaibleItems'=>$this->exportAvaibleItems(),
            'widget'=>$this->widgetSelector()
            // minLength und Delay wo kriegen wir die hier her?
          )
        )
      ;
    }
    
    return $this->createJooseSnippet(
      $this->jooseClass,
      $constructParams
    );
  }
  
  /**
   * @param collection<ComboDropBoxable> $avaibleItems
   * @chainable
   */
  public function setAvaibleItems($avaibleItems) {
    $this->acRequestMeta = NULL;
    $this->avaibleItems = $avaibleItems;
    return $this;
  }

  /**
   * @return collection
   */
  public function getAvaibleItems() {
    return $this->avaibleItems;
  }
  
  protected function exportAvaibleItems() {
    $export = array();
    
    if (count($this->avaibleItems) > 0 && !isset($this->entityMeta)) {
      throw new \Psc\Exception('EntityMeta muss übergeben werden wenn avaibleItems eine ItemsCollection ist');
    }
    
    foreach ($this->avaibleItems as $item) {
      // wir könnten hier auch checken ob es schon selectComboBoxable ist
      $export[] = $this->exportItem($item);
    }
    return $export;
  }
  
  protected function exportItem($item) {
    return $this->exporter->ComboDropBoxable($this->entityMeta->getAdapter($item));
    //return $this->exporter->SelectComboBoxable($this->entityMeta->getAdapter($item));
  }
  
  /**
   * Ist dies gesetzt wird avaibleItems ignoriert
   * @param Psc\CMS\AutoCompleteRequestMeta $autoCompleteRequestMeta
   * @chainable
   */
  public function setAutoCompleteRequestMeta(\Psc\CMS\AutoCompleteRequestMeta $autoCompleteRequestMeta) {
    $this->acRequestMeta = $autoCompleteRequestMeta;
    $this->avaibleItems = NULL;
    return $this;
  }

  /**
   * @return Psc\CMS\AutoCompleteRequestMeta
   */
  public function getAutoCompleteRequestMeta() {
    return $this->acRequestMeta;
  }


  /**
   * Setzt das Element welches in der ComboBox ausgewählt ist
   *
   */
  public function setSelected($item) {
    if (!isset($this->entityMeta)) {
      throw new \Psc\Exception('EntityMeta muss übergeben werden wenn selected gesetzt wird');
    }
    
    $ec = $this->entityMeta->getClass();
    if (!($item instanceof $ec)) {
      throw new \InvalidArgumentException('Parameter Item für setSelected() muss vom Typ: '.$ec.' sein. '.Code::varInfo($item).' wurde übergeben');
    }
    
    $this->selected = $item;
    return $this;
  }
  
  public function getSelected() {
    return $this->selected;
  }


  /**
   * @param string $name
   * @chainable
   */
  public function setName($name) {
    $this->name = $name;
    return $this;
  }

  /**
   * @return string
   */
  public function getName() {
    return $this->name;
  }

  /**
   * @param string $initialText
   * @chainable
   */
  public function setInitialText($initialText) {
    $this->initialText = $initialText;
    return $this;
  }

  /**
   * @return string
   */
  public function getInitialText() {
    return $this->initialText;
  }

  /**
   * @param bool $selectMode
   * @chainable
   */
  public function setSelectMode($selectMode) {
    $this->selectMode = $selectMode;
    return $this;
  }

  /**
   * @return bool
   */
  public function getSelectMode() {
    return $this->selectMode;
  }
  
  /**
   * @param integer $maxResults
   * @chainable
   */
  public function setMaxResults($maxResults) {
    if (isset($this->acRequestMeta))
      $this->acRequestMeta->setMaxResults($maxResults);
      
    return $this;
  }

  /**
   * Macht nur sinn wenn AutoCompleteRequestMeta auch gesetzt ist
   * @return integer|NULL 
   */
  public function getMaxResults() {
    if (!isset($this->acRequestMeta)) {
      return NULL;
    }
    
    return $this->acRequestMeta->getMaxResults();
  }
}
?>