<?php

namespace Psc\XML;

class Loader extends \Psc\Object {
  
  /**
   * Das zuletzt geladene SimpleXMLElement
   */
  protected $xml;
  
  /**
   * @var string
   */
  protected $raw;
  
  protected $rawLines;
  
  protected $rollbackInternal;
  
  public function __construct() {
  }
  
  public function process($xmlString) {
    $this->raw = $xmlString;
    $this->rawLines = explode("\n",$this->raw);

    // set + safe
    $this->rollbackInternal = libxml_use_internal_errors(TRUE);
    
    // process
    $this->xml = simplexml_load_string($this->raw, 'SimpleXMLElement', LIBXML_NOENT);
    
    // errors?
    if ($this->xml === FALSE) {
      return $this->processErrors();
    }
    
    $this->rollback();
    // return
    return $this->xml;
  }
  
  protected function processErrors() {
    $errors = libxml_get_errors();
    
    if (count($errors) == 0) {
      $this->rollback();
      throw new LoadingException('Fehler beim Laden des XMLS, es konnten aber keine Fehlerinformationen ermittelt werden.');
    }
    
    $cnt = count($errors);
    if ($cnt == 1)
      $msg = 'Beim Laden des XMLs ist ein Fehler aufgetreten: ';
    else
      $msg = sprintf('Beim Laden des XMLs sind %d Fehler aufgetreten: ',$cnt);
    
    $that = $this;
    $e = new LoadingException($msg."\n".\Psc\A::joinc($errors,"\n   %s\n\n",function ($error) use ($that) {
      return $that->errorToString($error);
    }));
    $e->libxmlErrors = $errors;
    
    libxml_clear_errors();
    $this->rollback();
    
    throw $e;
  }
  
  public function errorToString($error) {
    $context = array_key_exists($error->line-1,$this->rawLines) ? $this->rawLines[$error->line-1] : '[no context avaible]';
    $ret = NULL;

    switch ($error->level) {
      case LIBXML_ERR_WARNING:
        $ret .= "Libxml-Warning $error->code: ";
        break;
      case LIBXML_ERR_ERROR:
        $ret .= "Libxml-Error $error->code: ";
        break;
      case LIBXML_ERR_FATAL:
        $ret .= "Libxml-Fatal Error $error->code: ";
        break;
    }
    $ret .= trim($error->message).
      "\n  near: '".\Psc\String::cut($context,140,"'...").
      "\n  Line: $error->line" .
      "\n  Column: $error->column"
    ;
    
    return $ret;
  }
  
  protected function rollback() {
    libxml_use_internal_errors($this->rollbackInternal);
  }
}

?>