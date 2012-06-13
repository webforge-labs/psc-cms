<?php

namespace Psc\Data\Type;

use Psc\Data\Type\CompositeTypeTestCase;

/**
 * @group class:Psc\Data\Type\CompositeTypeTestCase
 */
class CompositeTypeTestCaseTest extends \Psc\Code\Test\Base {

  public function testDetectsNoComponentsDefined() {
    $testCase = new BadCompositeTypeTestCase('testAtLeastTwoComponentsDefined');
    
    $this->assertTest($testCase, $success = FALSE);
  }
  
  public function testRuns() {
    $testCase = new OKCompositeTypeTestCase('testAtLeastTwoComponentsDefined');
    
    $this->assertTest($testCase, $success = TRUE);
  }
}

class BadCompositeType extends CompositeType {
  
  public function __construct() {
    // hier wurde setComponents vergessen
  }
  
  protected function defineHint() {
    $this->phpHint = NULL; // ungetyped
  }
}

class BadCompositeTypeTestCase extends CompositeTypeTestCase {
  public function setUp() {
    $this->typeName = 'BadComposite';
  }
}

class OKCompositeType extends CompositeType {
  public function __construct() {
    $this->setComponents(new IntegerType(), new IntegerType());
  }

  protected function defineHint() {
    $this->phpHint = NULL; // ungetyped
  }
}

class OKCompositeTypeTestCase extends CompositeTypeTestCase {
  public function setUp() {
    $this->typeName = 'OKComposite';
  }
}

?>