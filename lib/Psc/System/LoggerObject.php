<?php

namespace Psc\System;

use Psc\Code\Code;

/**
 * class xxx extends \Psc\System\LoggerObject
 *
  public function __construct(\Psc\System\Logger $logger = NULL) {
    $this->setLogger($logger ?: new \Psc\System\BufferLogger());
  }
*/
class LoggerObject extends \Psc\SimpleObject {
  
  protected $logger;
  //protected $logLevel = 0;
  
  public function setLogger(\Psc\System\Logger $logger, $prefix = NULL) {
    $this->logger = $logger;
    $this->logger->setPrefix($prefix ?: '['.Code::getClassName($this).']');
    return $this;
  }
  
  /**
   * @return Psc\System\Logger
   */
  public function getLogger() {
    return $this->logger;
  }
  
  public function logf($msg) {
    return $this->log(vsprintf($msg, array_slice(func_get_args(),1)));
  }
  
  public function log($msg, $level = 1) {
    $this->logger->writeln($msg);
    return $this;
  }

  // ab detailLevel >= 5 wird der exceptionTrace mit geloggt
  public function logError(\Exception $e, $detailLevel = 1, $level = 1) {
    if ($detailLevel >= 5) {
      return $this->log("\n".'ERROR: '.\Psc\Exception::getExceptionText($e, 'text'));
    } else {
      return $this->log("\n".'ERROR: Exception: '.$e->getMessage(), $level);
    }
  }
  
  public function getLog() {
    return $this->logger->toString();
  }
}
?>