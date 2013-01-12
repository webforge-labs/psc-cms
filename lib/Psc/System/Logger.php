<?php

namespace Psc\System;

interface Logger {
  
  public function write($msg);
  
  public function writeln($msg);
  
  public function br();
  
  public function listenTo(DispatchingLogger $subLogger);
  
  public function setPrefix($prefix = NULL);
  
  public function toString();
}
?>