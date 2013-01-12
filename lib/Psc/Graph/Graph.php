<?php

namespace Psc\Graph;

/**
 *
 * Konventionen: die meisten Funktionen die als Parameter 2 Knoten erhalten können (z. B. add() oder remove())
 * beziehen sich bei 2 Parametern auf eine Kante in E und bei 1 Parameter auf einen Knoten aus V
 *
 */
class Graph extends \Psc\Object {
  
  /**
   * @var Vertice[]
   */
  protected $V;
  
  /**
   * @var Edges
   */
  protected $E;
  
  /**
   * Erstellt einen leeren Graphen
   */
  public function __construct() {
    $this->V = array();
    $this->E = new EdgesList();
  }
  
  /**
   * @return Edges
   */
  public function E() {
    return $this->E;
  }
  
  /**
   * @return array()
   */
  public function V() {
    return $this->V;
  }
  
  /**
   * Gibt die Nachbarschaft von $v zurück
   *
   * N(v) := { u | {u,v} \in E }
   * @return array
   */
  public function N(Vertice $v) {
    return $this->E->getAdjacent($v);
  }
  
  /**
   * Fügt Knoten/Kanten dem Graphen hinzu
   *
   * Sind $u und $v nicht im Graphen enthalten, werden sie hinzugefügt.
   * Sind beide Parameter übergeben wird zusätzlich eine Kante von $u nach $v hinzugefügt
   * 
   * @param Vertice $u
   * @param Vertice $v ist $v gesetzt wird eine Kante zwischen $u und $v hinzugefügt.
   */
  public function add(Vertice $u, Vertice $v = NULL) {
    
    /* wichtig hier ist dass wir nicht nur $this->addV($u) machen sondern auch die
      zurückgegebene reference für E->add() benutzen
      da sonst der Parameter zu E hinzugefügt wird, der nicht zwingend identisch mit der
      gespeicherten Vertice sein muss
      Dies ist Convenience da wir die label als identifier benutzen
    */
    
    $u = $this->addV($u);
    if (isset($v)) {
      $v = $this->addV($v);
      $this->E->add($u,$v);
    }
    return $this;
  }
  
  /**
   * Fügt dem Graphen einen Knoten hinzu
   *
   * @return Vertice gibt die eingefügte Vertice zurück
   */
  public function addV(Vertice $u) {
    if (!array_key_exists($u->label,$this->V))
      $this->V[$u->label] =& $u;
    
    return $this->V[$u->label];
  }
  
  /**
   * Entfernt einen Knoten/eine Kante aus dem Graphen
   *
   * @param Vertice $v ist $v gesetzt wird eine Kante zwischen $u und $v entfernt.
   */
  public function remove(Vertice $u, Vertice $v = NULL) {
    if (!isset($v)) {
      $this->E->erase($v); // entferne alle Kanten
      $this->remove($v);   // entferne den Knoten
    } else {
      $this->E->remove($u,$v); // entferne die Kante
    }
    return $this;
  }
  
  /**
   * @return bool
   */
  public function has(Vertice $u, Vertice $v = NULL) {
    if (!isset($v))
      return array_key_exists($u->label, $this->V);
    else
      return $this->E->has($u,$v);
  }
  
  /**
   * Gibt einen Knoten des Graphen zurück
   *
   * @return Vertice
   */
  public function get($input) {
    if ($input instanceof Vertice && $this->has($input)) {
      return $this->V[$input->label];
    } elseif (array_key_exists($input, $this->V)) {
      return $this->V[$input];
    } else {
      return NULL;
    }
  }
}