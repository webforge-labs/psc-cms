<?php

namespace Psc\System;

class EchoOutputBufferLogger extends EchoLogger {
  
  public function flush() {
    $flush = $this->toString();
    print $flush;
    $this->out .= $flush;
    ob_flush();
    flush();
    $this->reset();
  }
}