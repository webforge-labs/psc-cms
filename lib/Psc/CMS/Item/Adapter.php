<?php

namespace Psc\CMS\Item;

use Psc\CMS\Entity;
use Psc\CMS\EntityMeta;
use Psc\UI\TabButton;
use Psc\UI\DeleteTabButton;
use Psc\CMS\RequestMeta;
use Psc\CMS\RequestMetaInterface;

/**
 * Der Adapter verbindet die UI* Klassen mit EntityMeta + Entity
 *
 * z. B. 
 * new TabButton(new Adapter($entity, $entityMeta))
 *
 * der Adapter "bridged" ein Entity zu einem CMS\Item welches CMS\Item\*able Interfaces implementiert
 * Diese Interfaces werden in de UI* Klassen gehinted
 * 
 * der die UI Klasse benutzt dann die JooseBridge um einen inline JS Aufruf an das UI HTML Element anzuhängen
 * <button><button.data('joose-bridge', new Psc.CMS.Item({...}));
 * siehe auch JooseBridge für ein Beispiel
 * 
 * Achtung: für alle Context Parameter die übergeben werden können:
 *  NULL bedeutet nicht "default" sondern "lasse den Context so wie er ist"
 *
 * Interfaces Breakdown:
 *   SelectComboBoxable alias AutoCompletable
 */
class Adapter extends MetaAdapter implements ComboDropBoxable, TabButtonable, DeleteButtonable {
  
  /**
   * @var Psc\CMS\Entity
   */
  protected $entity;
  
  /* caches / vars */
  protected $tabButton, $tabRequestMeta, $tabLabel, $linkLabel;
  
  /**
   * Wird übergeben wenn der Context: action ist
   */
  protected $requestMetaAction;
  
  public function __construct(Entity $entity, EntityMeta $entityMeta, $context = 'default') {
    $this->setEntity($entity);
    parent::__construct($entityMeta,$context);
  }
  
  /**
   * Gibt einen Button zurück der einen Tab des Entities aufmachen kann (defaults nicht zu default ;))
   *
   * @return Psc\UI\ButtonInterface
   */
  public function getTabButton() {
    if (!isset($this->tabButton)) {
      if ($this->context === self::CONTEXT_DELETE) {
        $this->tabButton = new DeleteTabButton($this);
      } else {
        $this->tabButton = new TabButton($this);
      }
    }
    return $this->tabButton;
  }
  
  public function getDeleteTabButton() {
    return $this->getDeleteButtonable()->getTabButton();
  }
  
  public function getSelectComboBoxable() {
    return $this;
  }

  public function getComboDropBoxable() {
    return $this;
  }

  public function getAutoCompletable() {
    return $this;
  }

  public function getDeleteButtonable() {
    return $this->getAdapter(self::CONTEXT_DELETE);
  }
  
  /**
   * @param Psc\CMS\Entity $entity
   */
  public function setEntity(Entity $entity) {
    $this->entity = $entity;
    return $this;
  }
  
  /**
   * @return Psc\CMS\Entity
   */
  public function getEntity() {
    return $this->entity;
  }
  
  /* alle Item Interfaces für das ENtity benötigt wird (alle anderen sind in AdapterMeta */
  
  /**
   * @return mixed
   */
  public function getIdentifier() {
    return $this->entity->getIdentifier();
  }
  
  /**
   * der ShortName des Entities (aka type)
   *
   * @return string
   */
  public function getEntityName() {
    return $this->entityMeta->getEntityName();
  }
  
  
  /**
   * Gibt die RequestMeta für den aktuellen Kontext für ein Entity zurück
   *
   * bei context: action muss requestMetaAction gesetzt sein
   * @return Psc\CMS\RequestMeta
   */
  public function getTabRequestMeta() {
    if (!isset($this->tabRequestMeta)) {
      if ($this->context === self::CONTEXT_ACTION) {
        $this->tabRequestMeta = $this->entityMeta->getActionRequestMeta($this->requestMetaAction, $this->entity);
      } else {
        $this->tabRequestMeta = $this->entityMeta->getDefaultRequestMeta($this->entity);
      }
    }
    return $this->tabRequestMeta;
  }

  public function setTabRequestMeta(RequestMetaInterface $meta) {
    $this->tabRequestMeta = $meta;
    return $this;
  }

  public function getDeleteRequestMeta() {
    return $this->entityMeta->getDeleteRequestMeta($this->entity);
  }
  
  /**
   * @return string
   */
  public function getAutoCompleteLabel() {
    return $this->entity->getContextLabel(EntityMeta::CONTEXT_AUTOCOMPLETE);
  }
  
  /**
   * @return string
   */
  public function getButtonLabel() {
    if ($this->context === self::CONTEXT_DELETE) {
      return $this->entityMeta->getDeleteLabel($this->entity); // entityMeta entscheiden lassen, wie das label ist
    } elseif ($this->context === self::CONTEXT_ASSOC_LIST) {
      return $this->entity->getContextLabel(EntityMeta::CONTEXT_ASSOC_LIST);
    } elseif ($this->context === self::CONTEXT_GRID) {
      return $this->entity->getContextLabel(EntityMeta::CONTEXT_GRID);
    } else {
      return $this->entity->getContextLabel(EntityMeta::CONTEXT_BUTTON);
    }
  }

  /**
   * @return string|NULL
   */
  public function getButtonLeftIcon() {
    return $this->entity->getButtonLeftIcon();
  }

  /**
   * @return string|NULL
   */
  public function getButtonRightIcon() {
    return $this->entity->getButtonRightIcon();
  }
  
  /**
   * @return string
   */
  public function getFullButtonLabel() {
    return $this->entity->getContextLabel(EntityMeta::CONTEXT_FULL);
  }

  /**
   * Befragt das Entity mit getTabLabel() nach diesem label
   * @return string
   */
  public function getTabLabel() {
    if (isset($this->tabLabel)) {
      return $this->tabLabel;
    } else {
      return $this->entity->getContextLabel(EntityMeta::CONTEXT_TAB);
    }
  }

  /**
   * Das Label z.b. für RightContentLinkable
   */
  public function getLinkLabel() {
    return $this->linkLabel ?: $this->entity->getContextLabel($this->context); // sollte dann rightcontent sein oder so
  }


  /**
   * Erstellt einen neuen Adapter im richtigen Context
   *
   * @return Adapter mit dem angegebenen Context
   */
  protected function getAdapter($context) {
    return $this->context === $context || $context === NULL ? $this : new static($this->entity, $this->entityMeta, $context);
  }
  
  public function setTabLabel($label) {
    $this->tabLabel = $label;
    return $this;
  }
  
  /**
   * @param string $requestMetaAction
   * @chainable
   */
  public function setRequestMetaAction($requestMetaAction) {
    $this->requestMetaAction = $requestMetaAction;
    return $this;
  }

  /**
   * @return string
   */
  public function getRequestMetaAction() {
    return $this->requestMetaAction;
  }
  
  public function setLinkLabel($label) {
    $this->linkLabel = $label;
    return $this;
  }
}
?>