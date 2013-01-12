<?php

namespace Psc\Data;

use Psc\String AS S;

/**
 * Ein Prototype Set liefert Magic Methoden für ein normales Set um das Objekt als Prototypen für ein echtes Objekt nehmen zu können
 *
 * @see PrototypeSetTest
 */
class PrototypeSet extends \Psc\Data\Set {
  
  public function __call($method, array $params = array()) {
    $prop = mb_strtolower(mb_substr($method,3,1)).mb_substr($method,4); // ucfirst in mb_string
    
    if (s::startsWith($method,'get')) {
      
      return $this->get($prop);
    
    } elseif (s::startsWith($method, 'set')) {
      
      return $this->set($prop, $params[0]);
    
    } else {
      throw new \Psc\Exception('Undefined Method: '.__CLASS__.'::'.$method);
    }
  }
}
?>