<?php

namespace Psc\Data\Type;

use Psc\Data\Type\StringType;

/**
 * @group class:Psc\Data\Type\StringType
 */
class StringTypeTest extends TestCase {

  public function testConstruct() {
    $stringType = new StringType();
    
    $this->assertDocType('string', $stringType);
    $this->assertTypeMapsComponent('TextField', $stringType);
  }
}
?>