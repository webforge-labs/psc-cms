<?php

namespace Psc\CMS;

use Psc\UI\PanelButtons;
use Psc\CMS\Translation\Container as TranslationContainer;

/**
 * 
 */
class EntityViewPackage extends \Psc\SimpleObject {
  
  /**
   * @var Psc\CMS\EntityFormPanel
   */
  protected $formPanel;
  
  /**
   * @var Psc\CMS\EntityGridPanel
   */
  protected $gridPanel;
  
  /**
   * Ein ComponentMapper für die Komponenten des Entities
   * 
   * wird wenn createFormPanel() verwendet wird dem EntityFormPanel injected
   * @var Psc\CMS\ComponentMapper
   */
  protected $componentMapper;

  /**
   * @var Psc\CMS\Labeler
   */
  protected $labeler;
  
  /**
   * @var Psc\CMS\EntitySearchPanel
   */
  protected $searchPanel;
  
  /**
   * @var Psc\UI\PanelButtons
   */
  protected $panelButtons;

  /**
   * @var Psc\CMS\Translation\Container
   */
  protected $translationContainer;
  
  public function __construct(TranslationContainer $translationContainer, ComponentMapper $mapper = NULL, Labeler $labeler = NULL) {
    $this->translationContainer = $translationContainer;
    $this->componentMapper = $mapper ?: new ComponentMapper();
    $this->labeler = $labeler ?: new Labeler();
  }
  
  /**
   * @return Psc\CMS\EntityFormPanel
   */
  public function createFormPanel($label, EntityForm $entityForm, PanelButtons $buttons = NULL) {
    $this->formPanel = new EntityFormPanel(
      $label,
      $this->translationContainer,
      $entityForm,
      $this->componentMapper,
      $this->labeler
    );

    if ($buttons) {
      $this->formPanel->setPanelButtons($buttons);
    } elseif ($this->panelButtons) {
      $this->formPanel->setPanelButtons($this->panelButtons);
    }
    
    return $this->formPanel;
  }
  
  /**
   * @return Psc\CMS\EntityGridPanel
   */
  public function createGridPanel(EntityMeta $entityMeta, $label = NULL) {
    return $this->gridPanel = new EntityGridPanel($entityMeta, $this->translationContainer, $label, $this->labeler);
  }
  
  /**
   * @return Psc\CMS\EntitySearchPanel
   */
  public function createSearchPanel(EntityMeta $entityMeta, Array $query = NULL) {
    return $this->searchPanel = new EntitySearchPanel($entityMeta);
  }
  
  /**
   * @return Psc\CMS\EntityForm
   */
  public function createEntityForm(Entity $entity, RequestMeta $requestMeta) {
    return new EntityForm($entity, $requestMeta);
  }
  
  /**
   * @param Psc\CMS\FormPanel $formPanel
   * @chainable
   */
  public function setFormPanel(EntityFormPanel $formPanel) {
    $this->formPanel = $formPanel;
    return $this;
  }
  
  /**
   * @return Psc\CMS\EntityFormPanel
   */
  public function getFormPanel() {
    return $this->formPanel;
  }
  
  /**
   * @param Psc\CMS\ComponentMapper $componentMapper
   * @chainable
   */
  public function setComponentMapper(ComponentMapper $componentMapper) {
    $this->componentMapper = $componentMapper;
    return $this;
  }
  
  /**
   * @return Psc\CMS\ComponentMapper
   */
  public function getComponentMapper() {
    return $this->componentMapper;
  }
  
  /**
   * @param \Psc\CMS\Labeler $labeler
   * @chainable
   */
  public function setLabeler(Labeler $labeler) {
    $this->labeler = $labeler;
    return $this;
  }
  
  /**
   * @return \Psc\CMS\Labeler
   */
  public function getLabeler() {
    return $this->labeler;
  }
  
  /**
   * @param Psc\CMS\EntityGridPanel $gridPanel
   * @chainable
   */
  public function setGridPanel(EntityGridPanel $gridPanel) {
    $this->gridPanel = $gridPanel;
    return $this;
  }
  
  /**
   * @return Psc\CMS\EntityGridPanel
   */
  public function getGridPanel() {
    return $this->gridPanel;
  }
  
  /**
   * @param Psc\CMS\EntitySearchPanel $searchPanel
   */
  public function setSearchPanel(EntitySearchPanel $searchPanel) {
    $this->searchPanel = $searchPanel;
    return $this;
  }
  
  /**
   * @return Psc\CMS\EntitySearchPanel
   */
  public function getSearchPanel() {
    return $this->searchPanel;
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
    return $this->panelButtons;
  }
}
?>