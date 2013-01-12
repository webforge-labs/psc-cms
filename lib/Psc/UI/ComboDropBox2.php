<?php

namespace Psc\UI;

use Psc\UI\DropBox2 AS DropBox;
use Psc\UI\ComboBox2 AS ComboBox;

/**
 * http://wiki.ps-webforge.com/psc-cms:dokumentation:ui:combodropbox
 */
class ComboDropBox2 extends \Psc\HTML\JooseBase implements \Psc\JS\JooseSnippetWidget {
  
  /**
   * @var Psc\UI\ComboBox
   */
  protected $comboBox;
  
  /**
   * @var Psc\UI\DropBox
   */
  protected $dropBox;
  
  /**
   * Das Fieldset Label
   *
   * @var string
   */
  protected $label;
  
  public function __construct(ComboBox $comboBox, DropBox $dropBox, $groupLabel) {
    $this->comboBox = $comboBox;
    $this->dropBox = $dropBox;
    $this->label = $groupLabel;
    
    parent::__construct('Psc.UI.ComboDropBox');
  }
  
  protected function doInit() {
    $this->comboBox->disableAutoLoad();
    $this->dropBox->disableAutoLoad();
    
    $this->html = new Group($this->label, (object) array(
      'comboBox'=>$this->comboBox->html(),
      'dropBox'=>$this->dropBox->html()
    ));
    
    $this->html->addClass('\Psc\combo-drop-box-wrapper');
    
    $this->autoLoadJoose(
      $this->getJooseSnippet()
    );
  }
  
  public function getJooseSnippet() {
    return $this->createJooseSnippet(
      /* sieht ein bißchen lustig aus:
       * macht aber, dass die dropBox und comboBox widget-initialisierung als parameter von der comboDropBox-Initialisierung geladen werden
       * damit beheben wir asynchronous loading fehler und sparen uns ein script tag
       */
      $this->jooseClass, array(
        'dropBox'=>$this->dropBox->getJooseSnippet(),
        'comboBox'=>$this->comboBox->getJooseSnippet(),
        'dropBoxWidget'=>$this->widgetSelector($this->dropBox->html()),
        'comboBoxWidget'=>$this->widgetSelector($this->comboBox->html())
      )
    );
  }
  
  
  /**
   * @return Psc\UI\DropBox
   */
  public function getDropBox() {
    return $this->dropBox;
  }

  /**
   * @return Psc\UI\DropBox
   */
  public function getComboBox() {
    return $this->comboBox;
  }
}
?>