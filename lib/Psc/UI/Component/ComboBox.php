<?php

namespace Psc\UI\Component;

use Psc\CMS\EntityMeta;
use Psc\UI\ComboBox2 AS UIComboBox;
use Psc\CMS\AutoCompleteMeta;
use Psc\Doctrine\DCPackage;
use Psc\Form\SelectComboBoxValidatorRule;

class ComboBox extends Base implements DependencyComponent {
  
  /**
   * @var Psc\CMS\EntityMeta
   */
  protected $entityMeta;
  
  /**
   * @var Psc\Doctrine\DCPackage
   */
  protected $dc;
  
  /**
   * @var Psc\UI\ComboBox2
   */
  protected $comboBox;
  
  /**
   * @var Psc|CMS\AutoCompleteMeta|Traversable(Collection) von Entities
   */
  protected $avaibleItems;

  /**
   * EntityMeta des Items in der ComboBox
   */
  public function dpi(EntityMeta $entityMeta, DCPackage $dc) {
    $this->entityMeta = $entityMeta;
    $this->dc = $dc;
    return $this;
  }
  
  /**
   * @return Psc\HTML\HTMLInterface
   */
  public function getInnerHTML() {
    $input = $this->getComboBox()->html();
    \Psc\UI\Form::attachLabel($input,$this->getFormLabel());
    
    return $input;
  }
  
  
  /**
   * @return Psc\UI\ComboBox2
   */
  public function getComboBox() {
    if (!isset($this->comboBox)) {
      $this->comboBox = new UIComboBox($this->getFormName(), $this->getAvaibleItems(), $this->entityMeta);
      $this->comboBox->setSelectMode(TRUE); // damit das einzel item beim auswählen in der input box angezeigt wird
      
      // form value setzen
      if ($this->getFormValue() !== NULL) {
        $this->comboBox->setSelected($this->getFormValue());
      }
    }
    return $this->comboBox;
  }
  
  protected function initValidatorRule() {
    if (!isset($this->validatorRule)) { // wir lassen zu, dass die rule in sonderfällen von außen gesetzt wird
      $this->validatorRule = new SelectComboBoxValidatorRule($this->entityMeta->getClass(), $this->dc);
    }
  }
  
  /**
   * @return Psc|CMS\AutoCompleteMeta|Traversable(Collection) von Entities
   */
  public function getAvaibleItems() {
    if (!isset($this->avaibleItems)) {
      if (!($this->entityMeta instanceof \Psc\CMS\EntityMeta)) {
        throw new \Psc\Exception('Erst dpi() aufrufen oder avaibleItems setzen. (EntityMeta fehlt)');
      }
      
      $this->avaibleItems = $this->entityMeta->getAutoCompleteRequestMeta();
    }
    
    return $this->avaibleItems;
  }
  
  /**
   * @param Traversable $items
   */
  public function setAvaibleItems($items) {
    $this->avaibleItems = $items;
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
}
?>