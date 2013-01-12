<?php

class MetaWalker {
  
  protected $walkable;
  
  public function walkWalkableObject(Walkable $object) {
    $this->walkable = $object;
    foreach ($object->getWalkables() as $field => $value) {
      $this->walkField($field, $value);
    }
  }
  
  public function walkField($field, $value) {
    $meta = $this->walkable->getMeta($field);
    
    switch ($meta) {
      case 'string':
        $this->walkString($value, $field);
        break;
      
      case 'integer':
        $this->walkInteger($value, $field);
        break;
      
      case 'array':
        $this->walkArray($value, $field);
      // usw
    }
  }
  
  public function walkArray(Array $array, $field) {
    foreach ($array as $key => $value) {
      $this->walkArrayEntry($value, $key, $field, $array);
    }
  }
  
  public function walkString($string, $field) {
    // ...
  }

  public function walkInteger($int, $field) {
    // ...
  }
}
?>