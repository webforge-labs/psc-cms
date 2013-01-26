<?php

namespace Psc\CMS;

use Psc\Doctrine\DCPackage;
use Psc\UI\TabButton;
use Psc\CMS\Item\Buttonable;

/**
 * The Design Package helps for common tasks doing in the frontend for example in a controller
 *
 * 
 */
class DesignPackage {
  
  /**
   * @var Psc\Doctrine\DCPackage
   */
  protected $dc;
  
  /**
   *
   */
  public function __construct(DCPackage $dc) {
    $this->dc = $dc;
  }
  
  /**
   * @return Psc\CMS\Action|Psc\CMS\ActionMeta
   */
  public function action($entityOrMeta, $verb, $subResource = NULL) {
    if (is_string($entityOrMeta)) {
      $entityOrMeta = $this->dc->getEntityMeta($this->dc->expandEntityName($entityOrMeta));
    }
    
    return new Action($entityOrMeta, $verb, $subResource);
  }

  /**
   * @return Psc\UI\TabButtonInterface
   */
  public function tabButton($label, Action $action) {
    $adapter = $this->getEntityAdapterForAction($action);
    $adapter->setButtonLabel($label);
    
    $tabButton = new TabButton($adapter);
    
    return $tabButton;
  }
  
  protected function getEntityAdapterForAction(Action $action, $context = Item\MetaAdapter::CONTEXT_DEFAULT) {
    $entityMeta = $action->getEntityMeta($this->dc);
    if ($action->isSpecific()) {
      return $entityMeta->getAdapter($action->getEntity(), $context);
    }
    
    if ($action->isGeneral()) {
      return $entityMeta->getAdapter($context);
    }
  }
}
?>