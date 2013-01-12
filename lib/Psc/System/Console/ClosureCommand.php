<?php

namespace Psc\System\Console;

use Closure;

class ClosureCommand extends Command {
  
  protected $configure;
  protected $execute;
  
  public function __construct($name, Closure $configure, Closure $execute) {
    $this->configure = $configure;
    $this->execute = $execute;

    parent::__construct($name);
  }
  
  public function configure() {
    parent::configure();
    
    $configure = $this->configure;
    
    $definition = $configure($this);
    
    if (is_array($definition)) {
      $this->setDefinition($definition);
    }
  }
  
  public function doExecute($input, $output) {
    $execute = $this->execute;
    return $execute($input, $output, $this);
  }
}
?>