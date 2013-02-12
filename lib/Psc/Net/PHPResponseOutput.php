<?php

namespace Psc\Net;

class PHPResponseOutput implements ResponseOutput {
  
  protected $obFlush = FALSE;
  
  public function start() {
    // this will force internet explorer to not wait for the ouput when the tab / windows is NEW (reload always works as expected)
    $this->write(str_repeat(" ", 256));
    $this->flush();
    
    $this->obFlush = ob_get_level() > 0;
  }
  
  
  /**
   * Write something to the out
   */
  public function write($msg) {
    print $msg;
  }
  
  /**
   * Flushes the written output to the out
   *
   * use this to send progress in your response
   */
  public function flush() {
    flush();
    if ($this->obFlush) ob_flush();
  }
}
?>