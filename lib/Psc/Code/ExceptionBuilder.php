<?php

namespace Psc\Code;

class ExceptionBuilder extends \Psc\SimpleObject {
  
  protected $fqn;
  
  protected $msg;
  protected $previous = NULL;
  protected $code = 0;
  
  protected $vars = array();
  
  protected $exception;
  
  /**
   * Erstellt einen neuen ExceptionBuilder
   * 
   * Nach $formattedMessage können weitere Parameter übergeben werden die als Parameter für ein vsprintf() für $formattedMessage angesehen werden
   * @param array $messageParams
   */
  public function __construct($exceptionClassFQN, $formattedMessage, Array $messageParams = NULL) {
    $this->fqn = $exceptionClassFQN;
    $this->msg = isset($messageParams) ? vsprintf($formattedMessage, $messageParams) : $formattedMessage;
  }
  
  /**
   * Gibt die fertige Exception zurück
   * 
   * Nachdem end() aufgerufen wurde, haben die anderen Funktionen keinen Effekt mehr. Der Builder ist dann nutzlos
   * @return Exception
   */
  public function end() {
    $c = $this->fqn;
    $this->exception = new $c($this->msg, $this->code, $this->previous);
    foreach ($this->vars as $var => $value) {
      $this->exception->$var = $value;
    }
   
    return $this->exception;
  }
  
  public function getException() {  return $this->end(); }
  public function get() { return $this->end(); }
  
  public function set($var, $value) {
    $this->vars[$var] = $value;
    return $this;
  }
  
  public function setCode($code) {
    $this->code = $code;
    return $this;
  }
  
  public function setPrevious(\Exception $e) {
    $this->previous = $e;
    return $this;
  }
  
  public function setMessage($msg) {
    $this->msg = $msg;
  }
}
?>