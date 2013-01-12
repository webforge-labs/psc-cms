<?php

namespace Psc\UI;

/**
 * Ein FormPanel ist ein Wrapper (Fieldset) um eine Psc\CMS\Form
 *
 * ein Formpanel hat ein Label, weiteren Content (hinter der Form), die PanelButtons und soll mal ein Accordion für die rechte Seite haben
 */
class FormPanel extends \Psc\HTML\Base {
  
  /**
   * @var Psc\CMS\Form
   */
  protected $form;
  
  /**
   * @var Psc\HTML\Tag
   */
  protected $panel;
  
  protected $splitWidth = 60;
  
  /**
   * @var string
   */
  protected $label;
  
  /**
   * @var Psc\UI\PanelButtons
   */
  protected $panelButtons;
  
  /**
   * Zusätzlicher Content hinter der Form
   * 
   * @var array
   */
  protected $content = array();

  /**
   * Die Gesamtbreite des Panels
   */
  protected $width = '70%';
  
  /**
   * @var Psc\UI\Accordion
   */
  protected $accordion;
  
  /**
   * Die Anordnung der Komponenten
   *
   * interna: buttons, content
   * @var string
   */
  protected $contentTemplate;
  
  public function __construct($label, \Psc\CMS\Form $form = NULL, PanelButtons $panelButtons = NULL, Accordion $accordion = NULL) {
    $this->form = $form ?: new \Psc\CMS\Form();
    $this->label = $label;
    $this->panelButtons = $panelButtons ?: new PanelButtons(array('save','reload','save-close'));
    $this->contentTemplate =
      "\n".
      "  %buttons%\n".
      "  %content%\n".
      "\n"
    ;
    
    if (isset($accordion)) {
      $this->addAccordion($accordion);
    }
  }
  
  protected function doInit() {
    $this->panel = HTML::tag('div',
                       (object) array(
                          'buttons'=>$this->panelButtons->html(),
                          'content'=>$this->getContentLayout()
                        ),
                       array('class'=>'\Psc\form-panel')
                       )
                    ->setContentTemplate($this->contentTemplate)
                    ->setStyle('width', $this->width);
    
    // panel muss in die form damit alle elemente im <form> tag sind
    $this->form->setContent('panel', $this->panel);
    $this->html = $this->form->html();
  }
  
  protected function getContentLayout() {
    $content = $this->content;
    
    // entweder eine group oder ein div(wenn label nicht gesetzt) um den eigentlichen content herum
    if (isset($this->label)) {
      $contentGroup = new Group($this->label, $content);
      $contentGroup->getContentTag()->setStyle('min-height','350px');
    } else {
      $contentGroup = HTML::tag('div', $content);
      $contentGroup->setStyle('min-height','350px');
    }
    
    if (isset($this->accordion)) {
      return new SplitPane($this->splitWidth, $contentGroup, $this->accordion, 2);
      return $pane;
    } else {
      return $contentGroup;
    }
  }
  
  public function addAccordion(Accordion $accordion) {
    $this->accordion = $accordion;
    $this->accordion->addClass('right-accordion')->addClass('should-scroll');
    $this->width = '100%';
    return $this;
  }
  
  public function dontScrollAccordion() {
    if (isset($this->accordion))
      $this->accordion->removeClass('should-scroll');
  }
  
  /**
   * @return Psc\UI\Accordion|NULL
   */
  public function getRightAccordion() {
    return $this->accordion;
  }
  
  public function removeRightAccordion() {
    $this->accordion = NULL;
    return $this;
  }
  
  /**
   * Fügt beliebigen Content nach dem Formular hinzu
   *
   * geht leider nicht nach init()
   */
  public function addContent($content) {
    $this->content[] = $content;
    return $this;
  }
  
  /**
   * @return Psc\CMS\Form
   */
  public function getForm() {
    return $this->form;
  }
  
  /**
   * @param string $label
   * @chainable
   */
  public function setLabel($label) {
    $this->label = $label;
    return $this;
  }

  /**
   * @return string
   */
  public function getLabel() {
    return $this->label;
  }
  
  /**
   * @param integer $splitWidth
   * @chainable
   */
  public function setSplitWidth($splitWidth) {
    $this->splitWidth = $splitWidth;
    return $this;
  }

  /**
   * @return integer
   */
  public function getSplitWidth() {
    return $this->splitWidth;
  }

  
  /**
   * @param string $width
   * @chainable
   */
  public function setWidth($width) {
    $this->width = $width;
    return $this;
  }

  /**
   * @return string
   */
  public function getWidth() {
    return $this->width;
  }
  
  /**
   * @param array|Psc\UI\PanelButtons $panelButtons
   * @chainable
   */
  public function setPanelButtons($panelButtons) {
    if (is_array($panelButtons)) {
      $panelButtons = new PanelButtons($panelButtons);
    }
    
    $this->panelButtons = $panelButtons;
    return $this;
  }

  /**
   * @return Psc\UI\PanelButtons
   */
  public function getPanelButtons() {
    return $this->panelButtons;
  }
}
?>