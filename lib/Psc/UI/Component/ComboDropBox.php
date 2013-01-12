<?php

namespace Psc\UI\Component;

use Psc\Form\DropBox2ValidatorRule;
use Psc\Doctrine\DCPackage;
use Psc\CMS\EntityMeta;
use Psc\CMS\AutoCompleteRequestMeta;

use Psc\UI\ComboDropBox2;
use Psc\UI\DropBox2;
use Psc\UI\ComboBox2;

class ComboDropBox extends \Psc\UI\Component\ValuesBase implements DependencyComponent {
  
  // $this->values sind die Items die in der Combo-Box angezeigt werden
  protected $assignedItems = array();
  
  /**
   * @var Psc\UI\ComboDropBox2
   */
  protected $widget;
  
  /**
   * Die EntityMeta des Entities welches in der ComboDropBox ausgewählt wird
   *
   * @var Psc\CMS\EntityMeta
   */
  protected $entityMeta;

  /**
   * @var Psc\Doctrine\DCPackage
   */
  protected $dc;
  
  /**
   * Die RequestMeta für die ComboBox
   * 
   */
  protected $acRequestMeta;
  
  public function dpi(EntityMeta $entityMeta, DCPackage $dc, AutoCompleteRequestMeta $requestMeta = NULL) {
    $this->entityMeta = $entityMeta;
    $this->dc = $dc;
    if (isset($requestMeta)) {
      $this->requestMeta = $requestMeta;
    } else {
      $this->requestMeta = $this->entityMeta->getAutoCompleteRequestMeta();
    }
    return $this;
  }
  
  protected function doInit() {
    parent::doInit();
    $this->initWidget();
  }
  
  protected function initWidget() {
    if (!isset($this->entityMeta)) {
      throw new \Psc\Exception('EntityMeta muss für initWidget() gesetzt sein');
    }
   
    $this->widget = new ComboDropBox2($this->getComboBox(), $this->getDropBox(), $this->getFormLabel() ?: $this->entityMeta->getLabel());
  }
  
  /**
   * @return Psc\UI\ComboBox2
   */
  public function getComboBox() {
    if (!isset($this->comboBox)) {
      // requestmeta als avaibleitems (ajax loading)
      $this->comboBox = new ComboBox2($this->getFormName(), $this->requestMeta, $this->entityMeta);
    }
    
    return $this->comboBox;
  }
  
  /**
   * @return Psc\UI\DropBox2
   */
  public function getDropBox() {
    if (!isset($this->dropBox)) {
      $this->dropBox = new DropBox2($this->getFormName(), $this->entityMeta, $this->getFormValue());
    }
    return $this->dropBox;
  }

  protected function initValidatorRule() {
    $this->validatorRule = new DropBox2ValidatorRule($this->entityMeta, $this->dc);
  }
  
  /**
   * Die Items, die sich bereits in der DropBox befinden
   *
   * sowas wie value="" von einer textbox
   */
  public function getAssignedItems() {
    return $this->assignedItems;
  }
  
  public function setAssignedItems($items) {
    $this->assignedItems = $items;
    return $this;
  }
  
  /**
   * Die Items, die in der ComboBox sind und zur Auswahl stehen um zur DropBox hinzugefügt werden zu können
   *
   * @return Collection|NULL
   */
  public function getAvaibleItems() {
    return $this->getComboBox()->getAvaibleItems();
  }
  
  public function setAvaibleItems($items) {
    $this->getComboBox()->setAvaibleItems($items);
    return $this;
  }
  
  /**
   * @param integer $maxResults
   * @chainable
   */
  public function setMaxResults($maxResults) {
    $this->getComboBox()->setMaxResults($maxResults);
    return $this;
  }

  /**
   * @return integer
   */
  public function getMaxResults() {
    return $this->getComboBox()->getMaxResults();
  }
  
  /**
   * @chainable
   */
  public function setMultiple($bool) {
    $this->getDropBox()->setMultiple((bool) $bool);
    return $this;
  }
  
  /**
   * @return bool
   */
  public function getMultiple() {
    return $this->getDropBox()->getMultiple();
  }
  
  /**
   * @param bool $verticalButtons
   * @chainable
   */
  public function setVerticalButtons($verticalButtons) {
    $this->dropBox->setVerticalButtons($verticalButtons);
    return $this;
  }

  /**
   * @return bool
   */
  public function getVerticalButtons() {
    return $this->getDropBox()->getVerticalButtons();
  }


  
  public function getInnerHTML() {
    return $this->getWidget()->html();
  }

  public function getWidget() {
    if (!isset($this->widget)) {
      $this->initWidget();
    }
    return $this->widget;
  }
  
  /**
   * @param Psc\CMS\EntityMeta $entityMeta
   * @chainable
   */
  public function setEntityMeta(\Psc\CMS\EntityMeta $entityMeta) {
    $this->entityMeta = $entityMeta;
    return $this;
  }

  /**
   * @return Psc\CMS\EntityMeta
   */
  public function getEntityMeta() {
    return $this->entityMeta;
  }
}
?>