<?php

namespace Psc\CMS;

use Psc\CMS\EntityMetaProvider;

class ActionRouter {

  /**
   * @var Psc\CMS\EntityMetaProvider
   */
  protected $entityMetaProvider;
  
  public function __construct(EntityMetaProvider $entityMetaProvider) {
    $this->entityMetaProvider = $entityMetaProvider;
  }
  
  /**
   * @return Psc\CMS\RequestMetaInterface
   */
  public function route(Action $action) {
    return new SimpleRequestMeta($this->mapVerb($action->getVerb()), $this->generateUrl($action));
  }
  
  protected function mapVerb($verb) {
    return $verb; // as of GET|PUT|POST|DELETE are all strings constants everywhere
  }
  
  /**
   * @return string
   */
  protected function generateUrl(Action $action) {
    $entityMeta = $action->getEntityMeta($this->entityMetaProvider);
    
    $url = '/entities';
    
    if ($action->isSpecific()) {
      $url .= '/'.$entityMeta->getEntityName();
      $url .= '/'.$action->getEntity()->getIdentifier();
    } else {
      $url .= '/'.$entityMeta->getEntityNamePlural();
    }
    
    if ($action->hasSubResource()) {
      $url .= '/'.$action->getSubResource();
    }
    
    return $url;
  }
}
?>