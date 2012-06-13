<?php

namespace Psc\Data\Type;

use Psc\Data\Type\TextType;

/**
 * @group class:Psc\Data\Type\TextType
 */
class TextTypeTest extends TestCase {

  public function testMapsToSomeComponent() {
    $this->assertTypeMapsComponent('any', new TextType());
  }
}
?>