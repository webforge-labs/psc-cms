<?php

namespace Psc\JS\AST;

use stdClass;
use Webforge\Types\ObjectType;

class CodeWriterTest extends \Webforge\Code\Test\Base {
  
  public function setUp() {
    $this->chainClass = 'Psc\\JS\\AST\\CodeWriter';
    parent::setUp();

    $this->writer = new CodeWriter();
  }

  public function testWriteHashMapWithNoElements() {
    $this->assertEquals('{}', $this->writer->writeHashMap(new stdClass(), new ObjectType()));
  }
}
