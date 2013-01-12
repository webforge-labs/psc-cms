<?php

namespace Psc;

class ClassLoadException extends \Psc\Exception {
  
  public $class;
  
  public $searchPath;
  
  public $namespace;
  
  public $classPath;
  
}

?>