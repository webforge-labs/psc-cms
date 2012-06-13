<?php

namespace Psc\Data\Type;

use Psc\Data\Type\MarkupTextType;

/**
 * @group class:Psc\Data\Type\MarkupTextType
 */
class MarkupTextTypeTest extends TestCase {

  public function testMapsSome() {
    $this->assertTypeMapsComponent('any', new MarkupTextType());
  }
}
?>