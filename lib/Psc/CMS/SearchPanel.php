<?php

namespace Psc\CMS;

use Psc\UI\FormItemAutoCompleteAjax;
use Psc\UI\HTML;
use Psc\UI\Form AS f;
use stdClass;

/**
 * @TODO AutoComplete Stuff auslagern
 * @TODO Controller aus Tiptoi in Library zurückfact0rn
 * @TODO interface für item?
 */
class SearchPanel extends \Psc\HTML\JooseBase {
  
  /**
   * Die Headline der Form-Group
   * 
   * @var string
   */
  protected $label;
  
  /**
   * @var int
   */
  protected $autoCompleteMinLength;
  
  /**
   * @var int in ms
   */
  protected $autoCompleteDelay;
  
  /**
   * @var stdClass|NULL
   */
  protected $autoCompleteBody;
  
  /**
   * Ein Bag von Strings für den Panel
   * 
   * Beschreibung siehe __construct
   */
  protected $item;
  
  /**
   * Die komplette Beschreibung der Funktionsweise der Search Box (setter/getter injection)
   */
  protected $explanation;
  
  /**
   * Der Hint direkt unter der Textbox (setter/getter injection)
   * 
   * wird automatisch gefüllt wenn label und fields von item gesetzt sind
   */
  protected $hint;
  
  /**
   * @var integer
   */
  protected $maxResults;
  
  /**
   * 
   * Beispiel Sprecher:
   * 
   * $item->genitiv = "eines Sprechers" (Context: zum Suchen eines Sprechers den Namen eingeben)
   * $item->fields = 'Name, Identifier oder customField'
   * $item->label = 'Sprecher'  (Sprecher-Suche)
   * $item->type = 'speaker' entityName
   */
  public function __construct(stdClass $item) {
    
    if (!isset($item->fields)) {
      $item->fields = 'den Namen';
    }
    $this->item = $item;
    
    parent::__construct('Psc.UI.AutoComplete');
  }
  
  protected function doInit() {
    
    // das zuerst machen, da das html von f::inputSet bei html() schon gemorphed wird
    $widget = $this->getAutoCompleteWidget();
    $widgetSelector = \Psc\JS\jQuery::getClassSelector($widget);
    
    $content = NULL;
    $content .= f::inputSet(
      f::input(
        $widget,
        $this->getHint()
      )
    )->setStyle('width','90%')->html(); // gilt für alle
    
    
    // ist auch in ajax.dialog.add.speakers
    $content .= f::hint($this->getExplanation());
    
    $this->html = f::group($this->getLabel(),
                              $content,
                              f::GROUP_COLLAPSIBLE
                  )->setStyle('width','80%');
    
    $this->constructParams['widget'] = new \Psc\JS\Code($widgetSelector);
    $this->constructParams['delay'] = $this->getAutoCompleteDelay();
    $this->constructParams['minLength'] = $this->getAutoCompleteMinLength();
    $this->constructParams['url'] = $this->getAutoCompleteURL();
    $this->constructParams['maxResults'] = $this->getMaxResults();
    if ($this->getAutoCompleteBody() != NULL) {
      $this->constructParams['body'] = $this->getAutoCompleteBody();
    }
    $this->constructParams['eventManager'] = new \Psc\JS\Code('main.getEventManager()');
    
    $this->autoLoad();
  }
  
  public function getExplanation() {
    if (!isset($this->explanation)) {
      $this->explanation = (isset($this->item->genitiv) ?
          'Zum Suchen '.$this->item->genitiv.', '.$this->item->fields.' eingeben und anschließend einen Eintrag aus der Liste auswählen.'."\n"
        : 'Zum Suchen einen Suchbegriff eingeben und anschließend einen Eintrag aus der Liste wählen'."\n"
      ).
      'Durch die Auswahl eines Eintrages im Menü wird ein neuer Tab geöffnet.'."\n".
      'Nach dem Auswählen kann das Menü wieder mit der Pfeiltaste (nach unten) geöffnet werden, ohne eine neue Suche zu beginnen';
    }
    
    return $this->explanation;
  }
  
  public function getAutoCompleteURL() {
    return $this->item->url;
  }
  
  public function setExplanation($explanation) {
    $this->explanation = $explanation;
  }
  
  public function getAutoCompleteWidget($value = '') {
    // @TODO schöner wäre eine componente
    $input = f::text(NULL, 'identifier', $value)
        ->addClass('autocomplete')
        ->removeAttribute('id') // damit wir eine schöne guid erzeugen
    ;
    
    return $input;
  }
  
  public function getLabel() {
    if (!isset($this->label)) {
      $this->label = isset($this->item->label) ? sprintf('%s-Suche',$this->item->label) : 'Suche';
    }
    return $this->label;
  }
  
  public function setLabel($label) {
    $this->label = $label;
    return $this;
  }
  
  /**
   * @param string $hint
   * @chainable
   */
  public function setHint($hint) {
    $this->hint = $hint;
    return $this;
  }
  
  /**
   * @return string
   */
  public function getHint() {
    if (!isset($this->hint) && $this->item->fields && $this->item->label) {
      $minLengthHint = "\n".
        ($min = $this->getAutoCompleteMinLength()) == 1
          ? sprintf("Es mssen mindestens %d Zeichen eingegeben werden.", $min)
          : sprintf("Es müssen mindestens %d Zeichen eingegeben werden.", $min)
      ;
      
      $this->hint = sprintf("%s-Suche nach %s.\n%s", $this->item->label, $this->item->fields, $minLengthHint);
    }
    return $this->hint;
  }
  
  /**
   * @param int $autoCompleteMinLength
   * @chainable
   */
  public function setAutoCompleteMinLength($autoCompleteMinLength) {
    $this->autoCompleteMinLength = $autoCompleteMinLength;
    return $this;
  }
  
  /**
   * @return int
   */
  public function getAutoCompleteMinLength() {
    return $this->autoCompleteMinLength;
  }
  
  /**
   * @param int $autoCompleteDelay
   * @chainable
   */
  public function setAutoCompleteDelay($autoCompleteDelay) {
    $this->autoCompleteDelay = $autoCompleteDelay;
    return $this;
  }
  
  /**
   * @return int
   */
  public function getAutoCompleteDelay() {
    return $this->autoCompleteDelay;
  }
  
  /**
   * @param integer $maxResults
   */
  public function setMaxResults($maxResults) {
    $this->maxResults = $maxResults;
    return $this;
  }
  
  /**
   * @return integer
   */
  public function getMaxResults() {
    return $this->maxResults;
  }
  
  /**
   * @param stdClass $autoCompleteBody
   * @chainable
   */
  public function setAutocompleteBody(stdClass $autoCompleteBody) {
    $this->autoCompleteBody = $autoCompleteBody;
    return $this;
  }

  /**
   * @return stdClass
   */
  public function getAutocompleteBody() {
    return $this->autoCompleteBody;
  }


}
?>