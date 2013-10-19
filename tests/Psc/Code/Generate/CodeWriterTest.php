<?php

namespace Psc\Code\Generate;

use Psc\Code\AST;

class CodeWriterTest extends \Psc\Code\Test\Base {
  
  protected $codeWriter;

  public function setUp() {
    $this->codeWriter = new CodeWriter();
  }

  public function testCallGetter() {
    $this->assertEquals('$this->getIdentifier()', $this->codeWriter->callGetter(new AST\Variable('this'),'identifier'));
  }

  public function testCallSetter() {
    $this->assertEquals('$this->setIdentifier($id)', $this->codeWriter->callSetter(new AST\Variable('this'),'identifier',new AST\Variable('id')));
  }
}
