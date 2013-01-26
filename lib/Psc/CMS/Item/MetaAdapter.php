<?php

namespace Psc\CMS\Item;

use Psc\CMS\EntityMeta;
use Psc\CMS\Entity; // nur für den Hint

/**
 * Der Meta Adapter kümmert sich um alle item Interfaces, die nicht auch ein Entity benötigen
 *
 * Der Adapter verbindet die UI* Klassen mit EntityMeta + Entity (siehe auch Adapter)
 * new TabButton(new Adapter($entity, $entityMeta))
 * der Adapter "bridged" ein Entity zu einem CMS\Item welches CMS\Item\*able Interfaces implementiert
 * 
 * damit der Meta-Adapter z.b. beides mal Buttonable implementiert aber z. B. einen NewButton udn einen DefaultButton
 * adaptieren kann, gibt es einen context parameter. Dieser Parameter ist unveränderlich nach instanziierung und
 * steuert dann die rückgabe der einzelnen basic-interfaces
 * (so z.b. bei getTabRequestMeta() für context "default" => FormRequestMeta und für "new" dann NewFormRequestMeta)
 *
 * Achtung für alle Context Parameter die übergeben werden können:
 *  NULL bedeutet nicht "default" sondern "lasse den Context so wie er ist"
 */
class MetaAdapter extends \Psc\SimpleObject implements TabButtonable, RightContentLinkable {
  
  const CONTEXT_DEFAULT = 'default';
  const CONTEXT_ASSOC_LIST = 'assocList';
  const CONTEXT_GRID = 'grid';
  const CONTEXT_SEARCHPANEL = 'searchPanel';
  const CONTEXT_RIGHT_CONTENT = 'rightContent';
  const CONTEXT_NEW = 'new';
  const CONTEXT_ACTION = 'action';
  const CONTEXT_DELETE = 'delete';
  
  /**
   * @var Psc\CMS\EntityMeta
   */
  protected $entityMeta;
  
  /**
   * Immutable Context des Meta-Adapters
   *
   * zum Ändern einen neuen erzeugen.
   * @var string
   */
  protected $context;
  
  /**
   * Soll der Button per Drag oder Click geöffnet werden?
   * @see Buttonable
   * @var bitmap
   */
  protected $buttonMode;
  
  /* Caches */
  protected $newTabButton;
  
  protected $buttonLabel;
  
  public function __construct(EntityMeta $entityMeta, $context = 'default') {
    $this->setEntityMeta($entityMeta);
    $this->context = $context;
  }
  
  /**
   *
   * wegen strict geht das leider nicht schöner (kein Bock auf nicht ableitung)
   * @return MetaAdapter
   */
  public static function create(EntityMeta $entityMeta, Entity $entity = NULL, $context = 'default') {
    return new static($entityMeta, $context);
  }
  
  /**
   * @return Psc\UI\TabButton
   */
  public function getNewTabButton() {
    if (!isset($this->newTabButton)) {
      $this->newButton = new \Psc\UI\NewTabButton($this->getAdapter('new'));
    }
    
    return $this->newButton;
  }
  
  
  /**
   *
   * @return Psc\CMS\Item\RightContentLinkable
   */
  public function getRCLinkable($context = NULL) {
    return $this->getAdapter($context);
  }
  
  /**
   * @return Psc\CMS\Item\TabOpenable
   */
  public function getTabOpenable($context = NULL) {
    return $this->getAdapter($context);
  }
  
  /**
   * Erstellt einen neuen MetaAdapter im richtigen Context
   *
   * 
   * @return MetaAdapter mit dem angegebenen Context
   */
  protected function getAdapter($context) {
    return $this->context === $context || $context === NULL ? $this : new static($this->entityMeta, $context);
  }
  
  /**
   * @param Psc\CMS\EntityMeta $entityMeta
   */
  public function setEntityMeta(EntityMeta $entityMeta) {
    $this->entityMeta = $entityMeta;
    return $this;
  }
  
  /**
   * @return Psc\CMS\EntityMeta
   */
  public function getEntityMeta() {
    return $this->entityMeta;
  }
  
  
  /* Alle anderen Interfaces */
  
  /**
   * gibt die aktuelle RequestMeta für den Context zurück
   *
   * im Context New ist dies z.b. die newFormRequestMeta aus dem EntityMeta
   * @return Psc\CMS\RequestMeta
   */
  public function getTabRequestMeta() {
    if ($this->context === 'new') {
      return $this->entityMeta->getNewFormRequestMeta();
    } elseif ($this->context === 'grid') {
      return $this->entityMeta->getGridRequestMeta();
    } elseif ($this->context === self::CONTEXT_SEARCHPANEL) {
      return $this->entityMeta->getSearchPanelRequestMeta();
    } elseif ($this->context === self::CONTEXT_DEFAULT || $this->context === self::CONTEXT_ASSOC_LIST) {
      return $this->entityMeta->getDefaultRequestMeta();
    } else {
      throw new \InvalidArgumentException(sprintf("Context '%s' ist unbekannt",$this->context));
    }
  }

  /**
   * Das Label für den Tab selbst
   */
  public function getTabLabel() {
    if ($this->context === 'new') {
      return $this->entityMeta->getNewLabel();
    } elseif ($this->context === 'grid') {
      return $this->entityMeta->getGridLabel();
    } elseif ($this->context === self::CONTEXT_SEARCHPANEL) {
      return $this->entityMeta->getSearchLabel();
    } elseif ($this->context === self::CONTEXT_DEFAULT) {
      return $this->entityMeta->getLabel();
    } else {
      throw new \InvalidArgumentException(sprintf("Context '%s' ist unbekannt",$this->context));
    }
  }
  
  /**
   * Das Label z.b. für RightContentLinkable
   */
  public function getLinkLabel() {
    return $this->getTabLabel();
  }
  
  /**
   * Gibt das Label für den normalo Button zurück
   */
  public function getButtonLabel() {
    if (isset($this->buttonLabel)) {
      return $this->buttonLabel;
    } elseif ($this->context === 'new') {
      return $this->entityMeta->getNewLabel();
    } else {
      throw new \InvalidArgumentException(sprintf("Context '%s' ist unbekannt für getButtonLabel",$this->context));
    }
  }

  /**
   *
   */
  public function getButtonMode() {
    if (isset($this->buttonMode))
      return $this->buttonMode;
    
    if ($this->context === 'new') {
      return Buttonable::CLICK;
    } else {
      return Buttonable::DRAG;
    }
  }
  
  
  public function getButtonLeftIcon() {
    if ($this->context === 'new') {
      return 'circle-plus';
    }
    return NULL;
  }

  public function getButtonRightIcon() {
    return NULL;
  }

  /**
   *
   * für die RightContentLinkables, die kein Entity haben (z. B. gridPanel link, ist das hier okay)
   * @return mixed
   */
  public function getIdentifier() {
    return NULL;
  }
  
  /**
   * @return string
   */
  public function getEntityName() {
    return NULL;
  }
  
  /**
   * @return string|NULL
   */
  public function getFullButtonLabel() {
    return NULL;
  }
  
  /**
   * @var string
   */
  public function getContext() {
    return $this->context;
  }
  
  /**
   * @see Buttonable
   */
  public function setButtonMode($bitmap) {
    $this->buttonMode = $bitmap;
    return $this;
  }
  
  /**
   * @chainable
   */
  public function setButtonLabel($label) {
    $this->buttonLabel = $label;
    return $this;
  }
}
?>