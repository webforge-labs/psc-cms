<?php

namespace Psc\Net;

interface ResponseOutput {

  /**
   * Call this before first write()
   */
  public function start();
  
  /**
   * Write something to the out
   */
  public function write($msg);

  /**
   * Flushes the written output to the out
   *
   * use this to send progress in your response
   */
  public function flush();
}
?>