<?php

namespace Psc\CMS;

use stdClass;

/**
 * @TODO write a test nachdem searchpanel schöner ist
 */
class EntitySearchPanel extends \Psc\CMS\SearchPanel {
  
  public function __construct(EntityMeta $entityMeta, $maxResults = 15) {
    
    $acRequest = $entityMeta->getAutoCompleteRequestMeta(array('term'=>NULL));
    
    $item = new stdClass;
    $item->genitiv = $entityMeta->getGenitiv();
    $item->fields = \Psc\FE\Helper::listStrings($entityMeta->getAutoCompleteFields(), ', ', ' oder ');
    $item->type = $entityMeta->getEntityName();
    $item->url = $acRequest->getUrl();
    $item->label = $entityMeta->getLabel(EntityMeta::CONTEXT_AUTOCOMPLETE);
    $item->data = array();
    
    parent::__construct($item);
    
    $this->maxResults = $maxResults;
    
    // copy from meta
    // (hier die setter nehmen falls wir mal den search panel schön bauen sollten ;))
    $this->setLabel($entityMeta->getAutoCompleteHeadline());
    $this->setAutoCompleteDelay($entityMeta->getAutoCompleteDelay());
    $this->setAutoCompleteBody($acRequest->getBody());
    $this->setAutoCompleteMinLength($entityMeta->getAutoCompleteMinLength());
  }
}
?>