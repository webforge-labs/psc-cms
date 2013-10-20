<?php

namespace Psc\Data\Type;

class CompositeTypeTestCase extends \Webforge\Types\CompositeTypeTestCase {

  public function testConstruct() {
    $composite = Type::create($this->typeName);
    
    return $composite;
  }

}
