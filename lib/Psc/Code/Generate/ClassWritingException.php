<?php

namespace Psc\Code\Generate;

class ClassWritingException extends Exception {
  
  public $file;
  
  const OVERWRITE_NOT_SET = 4;
}

?>