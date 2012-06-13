<?php

namespace Psc\TPL;

use Psc\TPL\ASTCompiler;

/**
 * @group class:Psc\TPL\ASTCompiler
 */
class ASTCompilerTest extends \Psc\Code\Test\Base {

  public function testConstruct() {
    $compiler = new ASTCompiler(array());
    
  }
}
?>