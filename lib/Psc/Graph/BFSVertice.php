<?php

namespace Psc\Graph;

class BFSVertice extends ColorVertice {
    /**
   * Distance gebraucht in BFS
   * @var number 
   */
  public $d;
  
  /**
   * Vaterknoten, Information in BFS und DFS
   * @var GraphVertice
   */
  public $parent;
  
  /**
   * Die direkten Kinder der Vertice
   *
   * Kindeskinder muss man Rekursiv bestimmen
   * @var GraphVertices[]
   */
  public $childs = array();
  
  
  public function debugParents() {
    $ret = 'Parents: '.$this->getLabel().' (self)';
    
    foreach ($this->getParents(FALSE) as $parent) {
      $ret .= ' <- '.$parent->getLabel();
    }
    
    return $ret;
  }
  
  /**
   * Gibt die Eltern des Knoten zurücks
   *
   * Dabei werden auch die Eltern der Eltern des Knotens zurückgegeben (diese stehe im Array weiter hinten)
   * Die Node selbst ist nicht im Array enthalten wenn $withSelf FALSE ist
   */
  public function getParents($withSelf = TRUE) {
    $node = $this;
    $parents = array();
    while ($node->getParent() != NULL) {
      $parents[] = $node->getParent();
      $node = $node->getParent();
    }
    if ($withSelf) array_unshift($parents,$this);
    return $parents;
  }
}

