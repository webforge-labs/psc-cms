<?php

namespace Psc\CMS;

use Psc\Doctrine\DCPackage;
use Psc\UI\TabButton;

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
   * @return Psc\UI\ButtonInterface
   */
  public function tabButton($label, Action $action, $buttonMode = Buttonable::CLICK, $tabLabel = NULL) {
    $entityMeta = $action->getEntityMeta($this->dc);
    $button = $entityMeta->getAdapter();
  }
  
  protected function getEntityMetaForAction(ActionInterface $action) {
    if ($action->getType()
  }
}
?>