<?php

namespace Psc\Graph;

class DependencyVertice extends Vertice {
  
  protected $dependencies = array();
  protected $contextGraph;
  
  public $visited = FALSE;
  
  /**
   * Füge eine Dependency zu $dependency von dieser Vertice hinzu
   *
   *  eine topologische Sortierung eines DAG ist eine Lineare Anordnung all seiner Knoten mit der Eigenschaft
   *  dass u ($dependency) in der Anordnung vor v ($this) liegt (v ist abhängig von u), falls es eine Kante (u,v) gibt
   */
  public function dependsOn(Vertice $dependency) {
    $this->contextGraph->add($dependency, $this);
    $this->dependencies[] = $dependency;
  }
  
  /**
   * @param Psc\Graph\Graph $contextGraph
   * @chainable
   */
  public function setContextGraph(\Psc\Graph\Graph $contextGraph) {
    $this->contextGraph = $contextGraph;
    return $this;
  }

  /**
   * @return Psc\Graph\Graph
   */
  public function getContextGraph() {
    return $this->contextGraph;
  }

  /**
   * @return Array
   */
  public function getDependencies() {
    return $this->dependencies;
  }
  
  public function isVisited() {
    return $this->visited;
  }
  
  public function setVisited($status = TRUE) {
    $this->visited = $status;
    return $this;
  }
}
?>