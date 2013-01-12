<?php

namespace Psc\URL;

/**
 * Klassen die dieses Interface implementieren, sollten in der URLHelper Klasse bekannt sein!
 */
interface Routable {
  
  public function getRoutingURL($flags = 0x000000, Array $queryVars = array());
}