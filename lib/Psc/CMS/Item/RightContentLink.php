<?php

namespace Psc\CMS\Item;

use Psc\CMS\RequestMeta;

/**
 * Klasse für einen händischen RightContentLink
 *
 */
class RightContentLink extends \Psc\UI\Link implements RightContentLinkable {
  
  /**
   * @var Psc\CMS\RequestMeta
   */
  protected $tabRequestMeta;
  
  /**
   * Beide für Identifyable wenn der RightContent Link mit einem Entity Synchronisiert werden soll
   *
   * im Normalfall nicht nötig (dann lieber den Item Adapter benutzen!)
   */
  protected $identifier;
  protected $entityName;
  
  /**
   * Der label des Tabs (nicht des Links)
   *
   * wird keiner angegeben wird der des Links benutzt
   */
  protected $tabLabel;
  
  public function __construct(RequestMeta $tabRequestMeta, $label, $entityName = NULL, $entityIdentifier = NULL, $tabLabel = NULL) {
    parent::__construct($tabRequestMeta->getUrl(), $label);
    $this->tabRequestMeta = $tabRequestMeta;
    $this->entityName = $entityName;
    $this->identifier = $entityIdentifier;
    $this->tablabel = $tabLabel;
  }
  
  public function getLinkLabel() {
    return $this->getLabel();
  }
  
  public function getTabLabel() {
    return $this->tabLabel ?: $this->getLabel();
  }
  
  public function getTabRequestMeta() {
    return $this->tabRequestMeta;
  }
  
  public function getIdentifier() {
    return $this->identifier;
  }
  
  public function getEntityName() {
    return $this->entityName;
  }
  
  /**
   * @param Psc\CMS\RequestMeta $tabRequestMeta
   */
  public function setTabRequestMeta(RequestMeta $tabRequestMeta) {
    $this->tabRequestMeta = $tabRequestMeta;
    return $this;
  }
}
?>