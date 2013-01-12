<?php

namespace Psc\Graph;

class Edge extends \Psc\Object {
  
  /**
   * Startpunkt der Kante
   *
   * @var GraphVertice
   */
  protected $u;
  
  /**
   * Endpunkt der Kante
   *
   * @var GraphVertice
   */
  protected $v;
  
  
  /**
   * Das Gewicht der Kante
   * 
   * @var number
   */
  protected $w = 1;

  public function __construct(Vertice $u, Vertice $v) {
    $this->u = $u;
    $this->v = $v;
  }
}

?>