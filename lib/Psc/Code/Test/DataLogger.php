<?php

namespace Psc\Code\Test;

use \Webforge\Common\System\File,
    \Webforge\Common\System\Dir,
    \Psc\PSC,
    \Psc\TPL\TPL,
    \Psc\PHP\Writer
;

abstract class DataLogger extends \Psc\Object {
  
  /**
   * Der aktuelle Index für Neue Daten
   *
   * wird in den Daten als $log['index'] gespeichert
   */
  protected $index = 0;
  
  /**
   * Die Daten des Logs
   *
   * @var array
   */
  protected $data = array();
  
  
  protected $miniTemplate;
  
  /**
   * @var \Webforge\Common\System\File
   */
  protected $file;
  
  /**
   * @param string|File $dataFileName wenn ein String wird die Datei in base\files\testdata\logdata\$dataFileName.log.php gespeichert
   */
  public function __construct($dataFileName) {
    if ($dataFileName instanceof \Webforge\Common\System\File) {
      $this->file = $datafileName;
    } else {
      $dir = PSC::get(PSC::PATH_TESTDATA)->append('logdata/');
      $this->file = new File($dir, $dataFileName.'.log.php');
    }
    
    $this->miniTemplate = '<?php
/* Dies ist ein autogeneriertes TestLog von <'.$this->getClass().'> */
%index%

%data%

?>';
    
  }
  
  abstract public function log();

  /**
   * Fügt den Daten einen neuen Eintrag hinzu und erhöht den Index
   */
  protected function addLogData($data) {
    $this->data[$this->index++] = $data;
    
    return $this;
  }
  
  public function load() {
    @include $this->file; // kann ja auch leer sein
    
    if (isset($log['index'])) {
      $this->index = (int) $log['index'];
    }
    
    if (isset($log['data']) && is_array($log['data'])) {
      $this->data = $log['data'];
    }
    
    return $this;
  }
  
  public function write() {
    $contents = TPL::miniTemplate($this->miniTemplate,
                      array(
                        'index'=>Writer::variable(array('log','index'),$this->index),
                        'data'=>Writer::variable(array('log','data'), $this->data)
                      ));
    $dir = $this->file->getDirectory();
    if (!$dir->exists()) {
      $dir->make('-p');
    }
    
    $this->file->writeContents($contents, File::EXCLUSIVE);
    
    return $this;
  }
}
?>