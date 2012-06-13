<?php

namespace Psc\Code;

use \Psc\Code\Extracter;

/**
 * @group class:Psc\Code\Extracter
 */
class ExtracterTest extends \Psc\Code\Test\Base {

  public function testExtractFunctionBody() {
    
    $extracter = new Extracter();

$snippet = <<< 'FBODY'
  public function removeOID(OID $oid) {
    if ($this->oids->contains($oid)) {
      $this->oids->removeElement($oid);
    }
    
    return $this;
  }
FBODY;

    $actual = $extracter->extractFunctionBody($snippet);
    
    $this->assertEquals(Array(
      array(4,'if ($this->oids->contains($oid)) {'),
      array(6,'$this->oids->removeElement($oid);'),
      array(4,'}'),
      array(4,NULL),
      array(4,'return $this;')
    ), $actual);
  }
  
  public function testExtract_InterfaceFunction() {
    $extracter = new Extracter();

  $snippet = <<< 'FBODY'
  public function implementIt();
FBODY;

    $this->assertEquals(array(), $extracter->extractFunctionBody($snippet));
  }
    
  
  public function testExtract_InlineComment() {
    $extracter = new Extracter();
    
$snippet = <<< 'FBODY'
  public function removeOID(OID $oid) { // this gets into the body
    if ($this->oids->contains($oid)) { // is allowed
      $this->oids->removeElement($oid);
    }
     // is allowed, too
    $indents = "correct";
    
    return $this;
  }
FBODY;

    $actual = $extracter->extractFunctionBody($snippet);
    
    $this->assertEquals(Array(
      array(-1,'// this gets into the body'),
      array(4,'if ($this->oids->contains($oid)) { // is allowed'),
      array(6,'$this->oids->removeElement($oid);'),
      array(4,'}'),
      array(5,'// is allowed, too'),
      array(4,'$indents = "correct";'),
      array(4,NULL),
      array(4,'return $this;')
    ), $actual);
    
  }
}
?>