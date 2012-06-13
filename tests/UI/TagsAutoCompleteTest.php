<?php

namespace Psc\UI;

use \Psc\UI\TagsAutoComplete;

/**
 * @group class:Psc\UI\TagsAutoComplete
 */
class TagsAutoCompleteTest extends \Psc\Code\Test\Base {
  
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