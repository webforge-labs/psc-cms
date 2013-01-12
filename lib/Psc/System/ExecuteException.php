<?php

namespace Psc\System;

class ExecuteException extends Exception {
  public $cwd;
  
  public $env;
  
  public $stderr;
  
  public $stdout;
  
  /**
   * Ein String mit stderr und stdout formatiert
   */
  public $v;
  
}