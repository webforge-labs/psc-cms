<?php

namespace Psc\Code\AST;

use Webforge\Types\Type;

/**
 * Der Type (einer Variable)
 * 
 * der innere Typ ist unmutable
 */
class LType extends Element {
  
  /**
   * @var Psc\Data\Type\Type
   */
  protected $type;
  
  /**
   * Erstellt einen neuen LType - Wrapper
   * 
   * der Name des Types muss ein bestehender Psc\Data\Type\$nameType sein
   * siehe auch Psc\Data\Type\Type::create()
   */
  public function __construct($typeName) {
    $this->type = Type::create($typeName);
  }
  
  public function getName() {
    return $this->type->getName();
  }
  
  /**
   * @return Psc\Data\Type\Type
   */
  public function unwrap() {
    return $this->type;
  }
}
