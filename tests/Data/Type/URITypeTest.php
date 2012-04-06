<?php

namespace Psc\Data\Type;

use Psc\Data\Type\URIType;

class URITypeTest extends TestCase {

  public function testMapsSomeComponent() {
    $this->assertTypeMapsComponent('any', new URIType());
  }
}
?>