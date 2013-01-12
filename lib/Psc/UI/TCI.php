<?php

namespace Psc\UI;

use Psc\CMS\EntityMeta;
use Psc\CMS\Entity;
use stdClass;
use Psc\CMS\TabsContentItem2 AS tci2;

class TCI extends \Psc\HTML\JooseBase {

  public function __construct(EntityMeta $entityMeta, Entity $entity) {
    throw new \Psc\DeprecatedException('dont youse that anymore, use Psc\Item\Adapter');
    
    $requests = array(
      'grid'=>$entityMeta->getGridRequestMeta(),
      'new'=>$entityMeta->getNewRequestMeta(),
      'newForm'=>$entityMeta->getNewFormRequestMeta(),
      
      'search'=>$entityMeta->getSearchRequestMeta(array('filter'=>NULL)),
      'autoComplete'=>$entityMeta->getAutoCompleteRequestMeta(array('term'=>NULL)),
      
      'save'=>$entityMeta->getSaveRequestMeta($entity),
      'form'=>$entityMeta->getFormRequestMeta($entity),
      'delete'=>$entityMeta->getDeleteRequestMeta($entity),
    );
    
    $params = array('requests'=>new stdClass, 'labels'=>new stdClass);
    foreach ($requests as $req => $request) {
      $params['requests']->$req = array($request->getMethod(), $request->getUrl());
    }
    
    $contexts = array(
      'default'     =>tci2::LABEL_DEFAULT,
      'link'        =>tci2::LABEL_LINK,
      'tab'         =>tci2::LABEL_TAB,
      'button'      =>tci2::LABEL_BUTTON,
      'autoComplete'=>tci2::LABEL_AUTOCOMPLETE,
      'full'        =>tci2::LABEL_FULL
    );
    
    foreach ($contexts as $ctx => $context) {
      $params['labels']->$ctx = $entityMeta->getLabel($context);
    }
    
    $params['identifier'] = $entity->getIdentifier();
    parent::__construct('Psc.UI.TCI', $params);
  }
  
  public function attach(\Psc\HTML\Tag $tag) {
    
  }
  
  protected function doInit() {
    $this->autoLoad();
  }
}
?>