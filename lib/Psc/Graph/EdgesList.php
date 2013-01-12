<?php

namespace Psc\Graph;

/**
 * Implementierung von Edges als Adjazenzlisten
 */
class EdgesList extends \Psc\Object implements Edges {
  
  /**
   * Der Index ist das Label der Vertice,
   * der Inhalt eine Liste von Labels von adjazenten Vertices
   */
  protected $lists = array();
  
  
  public function add(Vertice $u, Vertice $v) {
    if (!array_key_exists($u->label, $this->lists)) {
      $this->lists[$u->label] = array();
    }
    $this->lists[$u->label][$v->label] = $v; // für MultiGraphen müsste hier [] benutzt werden
    return $this;
  }
  
  /**
   * Entfernt die Kante von $u und $v
   *
   * Ist $v nicht angegeben werden alle Kanten von $u entfernt
   */
  public function remove(Vertice $u, Vertice $v = NULL) {
    if ($v == NULL) return $this->erase($u);
    return $this;
  }
  
  /**
   * Löscht alle Kanten von $u
   */
  public function erase(Vertice $u) {
    if (isset($this->lists[$u->label]))
      unset($this->lists[$u->label]);
      
    return $this;
  }
  
  /**
   * @return bool
   */
  public function has(Vertice $u, Vertice $v = NULL) {
    if ($v != NULL) {
      return
        array_key_exists($u->label, $this->lists) &&
        array_key_exists($v->label, $this->lists[$u->label]);
    } else {
      return
        array_key_exists($u->label, $this->lists);
    }
  }
  
  /**
   * @return array Edges[]
   */
  public function getIncident(Vertice $v) {
    $ret = array();
    if ($this->has($v)) {
      foreach($this->lists[$v->label] as $u) {
        $ret[] = new Edge($v, $u);
      }
    }
    return $ret;
  }
  
  /**
   * @return array Vertices[]
   */
  public function getAdjacent(Vertice $v) {
    if ($this->has($v)) {
      return $this->lists[$v->label];
    } else {
      return array();
    }
  }
  
  
  public function debug() {
    $ret = "EdgesList (\n";
    
    foreach ($this->lists as $vLabel => $list) {
      $ret .= '  '.$vLabel.': [';
      $ret .= A::join($list,"'%s', ");
      $ret .= ']'."\n";
    }
    
    $ret .= ')'."\n";
    return $ret;
  }
  
  public function __toString() {
    return $this->debug();
  }
}