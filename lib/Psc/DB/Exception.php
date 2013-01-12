<?php

namespace Psc\DB;

class Exception extends \Psc\Exception { public $sql; }
class DuplicateKeyException extends Exception {}
class NoSuchTableException extends Exception {}


class MisconfigurationException extends Exception {
  
  /**
   * @param string $message der inhalt von $con (z. B. default)
   */
  public function __construct ($message = "", $code = 0, Exception $previous = NULL) {
    $con = $message;
    $this->message = 'Datenbank['.$con.'] nicht konfiguriert. Es fehlende möglicherweise die Konfigurationsvariablen: '."\n";
    
    foreach (array('host'=>"'localhost'",
                   'user'=>"''",
                   'password'=>"''",
                   'database'=>"''",
                   'port'=>"NULL",
                   'charset'=>"'utf8'") as $item => $value) {
      $this->message .= '$'."GLOBALS['conf']['db']['".$con."']['".$item."'] = ".$value.";\n";
    }
  }
}


?>