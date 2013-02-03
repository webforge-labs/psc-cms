<?php

namespace Psc\UI;

use Psc\Doctrine\DCPackage;
use Psc\UI\TabButton;
use Psc\CMS\Item\Buttonable;
use Psc\CMS\Action;
use Psc\CMS\ActionRouter;
use Psc\CMS\Item\MetaAdapter;
use Psc\CMS\EntityMetaProvider;

/**
 * The UI Package helps for common tasks doing in the frontend for example in a controller
 *
 */
class UIPackage {
  
  /**
   * @var Psc\CMS\EntityMetaProvider
   */
  protected $entityMetaProvider;
  
  /**
   * @var Psc\CMS\ActionRouter
   */
  protected $actionRouter;
  
  /**
   *
   */
  public function __construct(ActionRouter $router, EntityMetaProvider $entityMetaProvider) {
    $this->actionRouter = $router;
    $this->entityMetaProvider = $entityMetaProvider;
  }
  
  /**
   * @return Psc\CMS\Action|Psc\CMS\ActionMeta
   */
  public function action($entityOrMeta, $verb, $subResource = NULL) {
    if (is_string($entityOrMeta)) {
      $entityOrMeta = $this->entityMetaProvider->getEntityMeta($entityOrMeta);
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
    $tabButton->setTabRequestMeta($this->actionRouter->route($action));
    
    return $tabButton;
  }
  
  protected function getEntityAdapterForAction(Action $action, $context = MetaAdapter::CONTEXT_DEFAULT) {
    $entityMeta = $action->getEntityMeta($this->entityMetaProvider);
    if ($action->isSpecific()) {
      return $entityMeta->getAdapter($action->getEntity(), $context);
    }
    
    if ($action->isGeneral()) {
      return $entityMeta->getAdapter($context);
    }
  }
}
?>