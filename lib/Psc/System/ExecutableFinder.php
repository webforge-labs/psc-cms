<?php

namespace Psc\System;

use Psc\CMS\Configuration;
use Psc\PSC;

/**
 * Sucht nach Executables im aktuellen System
 *
 * benutzt die Configs und which (als Fallback)
 */
class ExecutableFinder {
  
  protected $config;
  
  public function __construct(Configuration $config = NULL) {
    $this->config = $config ?: PSC::getProject()->getConfiguration();
  }
  
  /**
   * @return File
   * @throws NoExecutableFoundException
   */
  public function getExecutable($name) {
    
    // Zuerst Config versuchen
    $cmd = $this->getConfigExecutable($name);
    if ($cmd === NULL) {
      $cmd = System::which($name, System::DONTQUOTE);
    }
    
    if ($cmd == '') {
      throw new NoExecutableFoundException(sprintf("Für den Namen '%s' kann kein Executable gefunden werden. Entweder muss executables.$name als absoluter Pfad in der Config gesetzt sein, oder der Befehl im Pfad liegen", $name));
    }
    
    $file = new File($cmd);
    if (!$file->exists()) {
      throw new NoExecutableFoundException(sprintf("Für den Namen '%s' wurde der Pfad: '%s' gefunden. Die Datei existiert aber nicht. Entweder muss executables.$name als absoluter Pfad in der Config korrekt gesetzt sein, oder der Befehl im Pfad liegen", $name, $cmd));
    }
    
    return $file;
  }
  
  /**
   * @return bool
   */
  public function findsExecutable($name) {
    try {
      $file = $this->getExecutable($name);
      return TRUE;
    
    } catch (NoExecutableFoundException $e) {
      return FALSE;
    }
  }
  
  /**
   * @return String|NULL
   */
  protected function getConfigExecutable($name) {
    if (is_array($name)) $name = implode('.',$name);
    return $this->config->get('executables'.'.'.$name);
  }
}
?>