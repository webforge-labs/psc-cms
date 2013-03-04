<?php

namespace Psc\System\Console;

use Psc\System\System;
use Webforge\Common\System\File;
use Psc\System\ExecuteException AS SystemExecuteException;

class MySQLLoader extends \Psc\Object {
  
  const JUST_RETURN = TRUE;
  
  protected $database;
  
  protected $file;
  
  protected $conf;
  
  /**
   * Der zuletzte ausgeführte Befehl
   *
   * @var string
   */
  protected $cmd;
  
  /**
   * @param Configuration $conf mit user password host port
   */
  public function __construct($database, \Psc\CMS\Configuration $conf) {
    $this->database = $database;
    $this->conf = $conf;
  }

  public function loadFromFile(File $sqlFile, $justReturn = FALSE) {
    $this->file = $sqlFile;
    
    //mysql --host=%s --user=%s --password=%s <database> < <file>
    $cmd = System::which('mysql').' %s%s < %s';
    $this->cmd = sprintf($cmd, \Webforge\Common\ArrayUtil::join($this->getOptions(), '--%2$s=%1$s '), $this->database, (string) $this->file);
    
    try {
      if ($justReturn)
        return $this->cmd;
      else {
        exec($this->cmd. ' 2>&1',$output, $ret);
       
       if ($ret != 0) {
        $e = new SystemExecuteException();
        $e->stderr = implode("\n",$output);
        throw $e;
       }
      }
    } catch (SystemExecuteException $e) {
      $ex = new MySQLLoaderException('Fehler beim Dumpen über Console: "'.trim($e->stderr)." Befehl:\n".$this->cmd);
      $ex->sqlError = $e->stderr;
      throw $ex;
    }
    
    return $this;
  }
  
  /**
   * @return array
   */
  public function getOptions() {
    $options = array();
    foreach (array('host','user','password','port') as $option) {
      if (($optionValue = $this->conf->get($option)) != '') {
        $options[$option] = $optionValue;
      }
    }
    return $options;
  }
}

?>