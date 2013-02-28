<?php

namespace Psc\CMS\Service;

use Psc\CMS\Item\TabOpenable;
use Psc\CMS\Item\Exporter;
use Psc\CMS\Entity;
use stdClass;

class MetadataGenerator extends \Psc\SimpleObject {
  
  protected $meta;
  
  public function __construct(Exporter $exporter = NULL) {
    $this->meta = array();
    $this->meta['data'] = new stdClass;
    $this->exporter = $exporter ?: new Exporter;
  }
  
  public function validationError(\Psc\Form\ValidatorException $e) {
    if (!isset($this->meta['validation']))
      $this->meta['validation'] = array();
      
    $this->meta['validation'][] = array(
      'msg'=>$e->getMessage(),
      'field'=>$e->field,
      'data'=>$e->data,
      'label'=>$e->label
    );
    
    return $this;
  }

  public function validationList(\Psc\Form\ValidatorExceptionList $list) {
    foreach ($list->getExceptions() as $validatorException) {
      $this->validationError($validatorException);
    }
    
    return $this;
  }
  
  /**
   * Adds metadata for the reponse from an Entity
   * 
   * @param Psc\Net\Service\LinkRelation[] $linkRelations
   */
  public function entity(Entity $entity, Array $linkRelations = array()) {
    if (!isset($this->meta['links']))
      $this->meta['links'] = array();
      
    foreach ($linkRelations as $linkRelation) {
      $this->meta['links'][] = (object) array(
        'rel'=>$linkRelation->getName(),
        'href'=>$linkRelation->getHref()
      );
    }
    
    return $this;
  }

  public function revision($revision) {
    $this->meta['revision'] = $revision;
    
    return $this;
  }
  
  public function openTab(TabOpenable $item) {
    $this->meta['data'] = (object) array_merge((array) $this->meta['data'],
                                      $this->exporter->TabOpenable($item)
                                     );
    return $this;
  }
  
  public function autoCompleteMaxResultsHit($maxResults) {
    // siehe Psc.UI.AutoComplete
    $this->meta['acInfo']['maxResultsHit'] = $maxResults;
    return $this;
  }
  
  public function toArray() {
    return $this->meta;
  }
  
  public function toHeaders() {
    return array(
      'X-Psc-Cms-Meta'=>json_encode($this->meta)
    );
  }
  
  public static function create() {
    return new static();
  }
}
?>