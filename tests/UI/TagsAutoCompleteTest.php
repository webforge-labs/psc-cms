<?php

use \Psc\UI\TagsAutoComplete;

class TagsAutoCompleteTest extends PHPUnit_Framework_TestCase {
  
  protected $c = '\Psc\UI\TagsAutoComplete';
  
  protected $ac;
  
  public function testConstruct() {
    $this->ac = new $this->c();
  }
  //
  //public function testHTML() {
  //  $this->ac = new $this->c();
  //  
  //  $tags = new ArrayCollection();
  //  
  //  
  //  
  //}
}

?>