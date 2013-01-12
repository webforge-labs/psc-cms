<?php

namespace Psc\Code;

use \Psc\PSC
;

class ErrorHandler {
  
  protected $recipient = NULL;

  private static $errors = array (
      E_ERROR              => 'Fatal Error',
      E_WARNING            => 'Warning',
      E_PARSE              => 'E_PARSE',
      E_NOTICE             => 'Notice',
      E_CORE_ERROR         => 'E_CORE_ERROR',
      E_CORE_WARNING       => 'E_CORE_WARNING',
      E_COMPILE_ERROR      => 'E_COMPILE_ERROR',
      E_COMPILE_WARNING    => 'E_COMPILE_WARNING',
      E_USER_ERROR         => 'Fatal Error',
      E_USER_WARNING       => 'Warning',
      E_USER_NOTICE        => 'Notice',
      E_STRICT             => 'Strict Standards',
      E_RECOVERABLE_ERROR  => 'Catchable Fatal Error',
      E_DEPRECATED         => 'E_DEPRECATED'
      );
  
  public function handle($number, $string, $file, $line, $context) {
    if (!($number & error_reporting())) return FALSE;
    
    // wir wandeln alle Fehler in Exceptions um
    $e = new \ErrorException($string, 0, $number, $file, $line);
    //$e->contextDump = \Psc\Doctrine\Helper::getDump($context,2);
    
    if ($number !== 0) {
      throw $e;
    }
  }
  
  protected function log(\Exception $e, $contextInfo = NULL) {
    if ($e instanceof \ErrorException) {
      $errorType = self::$errors[$e->getSeverity()];
    } else {
      $errorType = Code::getClass($e);
    }

    // wir müssen hier den error selbst loggen, da php nichts mehr macht (die faule banane)
    $php = NULL;
    $php .= 'PHP '.$errorType.': '.$e->getMessage().' in '.$e->getFile().' on line '.$e->getLine()."\n";
    $php .= $errorType.': '.\Psc\Exception::getExceptionText($e,'text')."\n";    
    error_log($php,0);
    
    /* Debug-Mail */
    $debug = NULL;
    $debug .= '['.date('d.M.Y H:i:s')."] ";
    $debug .= $errorType.': '.\Psc\Exception::getExceptionText($e,'text')."\n";
    
    if ($e instanceof \Psc\Code\ErrorException) {
      $debug .= "\n".$e->contextDump;
    }
    
    if (isset($contextInfo)) {
      $debug .= "\nContextInfo: \n".$contextInfo;
    }
    
    if (isset($this->recipient) && !PSC::inTests()) {
      if ($ret = @mail($this->recipient,
           '[Psc-ErrorHandler] ['.$e->getCode().'] '.$e->getMessage(),
           $debug,
           'From: www@'.PSC::getEnvironment()->getHostName()."\r\n".
           'Content-Type: text/plain; charset=UTF-8'."\r\n"
           ) === FALSE) {
        error_log('[\Psc\Code\ErrorHandler.php:'.__LINE__.'] Die Fehlerinformationen konnten nicht an den lokalen Mailer übergeben werden.',0);
      }
    }
  }
  
  /**
   * Bedeutet, dass der ErrorHandler den Programmfluss nicht unterbricht, aber z. B. mailt..
   *
   *  die Exception wird quasi von der Applikation behandelt, das mailen soll aber z. B. geschehen
   */
  public function handleCaughtException(\Exception $e, $contextInfo = NULL) {
    return $this->log($e, $contextInfo);
  }
  
  
  public function noticeSilentError(\Exception $e) {
    return $this->log($e);
  }
  
  public function register() {
    set_error_handler(array($this,'handle'),E_ALL | E_STRICT);
    return $this;
  }
  
  public function unregister() {
    restore_error_handler();
    return $this;
  }
  
  /**
   * @param string $recipient
   * @chainable
   */
  public function setRecipient($recipient) {
    $this->recipient = $recipient;
    return $this;
  }

  /**
   * @return string
   */
  public function getRecipient() {
    return $this->recipient;
  }
}
?>