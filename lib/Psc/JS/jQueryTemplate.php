<?php

namespace Psc\JS;

class jQueryTemplate extends \Psc\HTML\Tag {
  
  public function __construct($content, Array $attributes = array()) {
    parent::__construct('script', $content, $attributes);
    $this->setAttribute('type','x-jquery-tmpl');
  }
  
  public static function create($content, Array $attributes = array()) {
    return new static($content, $attributes);
  }
}
?>