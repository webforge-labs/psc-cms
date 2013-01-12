<?php

namespace Psc\System;

/**
 *
 * Man sollte prefix und eol als erstes setzen.
 * setzt man diese settings während des loggens ändern sich die anderen eol / prefixe nicht
 *
 * einmal geschriebene Zeichen, verlassen den Stream nicht mehr
 */
class BufferLogger extends AbstractLogger implements DispatchingLogger {
  
  protected $log = '';
  
  protected $eol = "\n";
  
  public function write($msg) {
    $this->log .= ($msg = $this->prefixMessage($msg));
    $this->dispatch('write',$msg);
    return $this;
  }
  
  public function writeln($msg) {
    $this->log .= ($msg = $this->prefixMessage($msg).$this->eol);
    $this->dispatch('writeln',$msg);
    return $this;
  }
  
  public function br() {
    $this->log .= $this->eol;
    
    $this->dispatch('br',"\n");
    return $this;
  }
  
  public function toString() {
    return $this->log;
  }
  
  public function setEOL($eol) {
    $this->eol = $eol;
    return $this;
  }
  
  public function setPrefix($prefix = NULL) {
    $this->prefix = $prefix != '' ? $prefix.' ' : NULL;
    return $this;
  }
  
  public function reset() {
    $this->log = '';
    return $this; 
  }
  
  public function __toString() {
    return (string) $this->toString();
  }
  
  /**
   *
   * führt reset() aus
   * @return string
   */
  public function flush() {
    $s = $this->toString();
    $this->reset();
    return $s;
  }
  
}
?>