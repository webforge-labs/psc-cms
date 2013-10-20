<?php

namespace Psc\Data\Type;

/**
 * @group class:Psc\Data\Type\CodeType
 */
class CodeTypeTest extends TestCase {
  
  public function testMapsToCodeEditorComponent() {
    $this->assertTypeMapsComponent('CodeEditor', new CodeType());
  }
}
?>