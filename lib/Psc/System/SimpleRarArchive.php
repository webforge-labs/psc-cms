<?php

namespace Psc\System;

use Webforge\Common\String AS S;
use Webforge\Common\System\File AS WebforgeFile;
use Webforge\Common\System\Dir AS WebforgeDir;

class SimpleRarArchive extends \Psc\Object {
  
  /**
   * Webforge\Common\System\File
   */
  protected $rar;
  
  /**
   * Webforge\Common\System\File
   */
  protected $bin;
  
  protected $finder;
  
  public function __construct(WebforgeFile $rar, ExecutableFinder $finder = NULL) {
    $this->rar = $rar;
    
    $this->finder = $finder ?: new ExecutableFinder();
    $this->bin = $this->finder->getExecutable('rar');
  }
  
  /**
   * Entpackt alle Dateien des Archives in ein Verzeichnis
   *
   * wird das Verzeichnis nicht angegeben, so wird das Verzeichnis genommen in dem sich das Archiv befindet
   * existiert das Verzeichnis nicht, wird es angelegt
   */
  public function extractTo(WebforgeDir $dir = NULL) {
    if (!isset($dir))
      $dir = $this->rar->getDirectory();
    else
      $dir->create();
    
    $this->exec('x', array('y'), (string) $dir);
    return $this;
  }
  
  /**
   * @param $fileLocator ein string wie er aus listFiles herauskommt
   * @param File $destination der Ort an den die Datei kopiert werden soll
   */
  public function extractFile($fileLocator, WebforgeFile $destination) {
    $tmp = Dir::createTemporary();
    $this->exec('x', array('y'), escapeshellarg($fileLocator).' '.escapeshellarg($tmp));
    
    // suche $file im $tmp verzeichnis
    $file = File::createFromURL($fileLocator, $tmp);
    if (!$file->exists()) {
      throw new Exception(
        sprintf("Entpacken hat zwar geklappt, aber die Datei '%s' kann nicht in '%s' (temporary) gefunden werden.",
                (string) $fileLocator,
                $tmp
                )
      );
    }
    $file->copy($destination);
    $tmp->delete();
    
    return $this;
  }
  
  /**
   * @return array mit den Werten als relative Pfade getrennt mit / (unsortiert)
   */
  public function listFiles() {
    $out = $this->exec('vb');
    $out = S::fixEOL($out);
    
    $files = explode("\n",$out);
    $files = array_filter($files);
    $files = array_map(function ($file) {
      return str_replace(DIRECTORY_SEPARATOR, '/', $file);
    }, $files);
    
    return $files;
  }
  
  /**
   * @param string $command nur der befehl oder mehrere (z. B. lb für list simple)
   * @param array $options alle optionen ohne - davor
   */
  protected function exec($command, Array $options = array(), $append = NULL) {
    $cmd = sprintf('%s %s%s %s%s',
                   System::escapeExecutable($this->bin),
                   $command,
                   (count($options) > 0 ? \Webforge\Common\ArrayUtil::join($options,' -%s') : NULL), // das letzte ist schalterbearbeitung abschließen
                   (string) escapeshellarg($this->rar),
                   (mb_strlen($append) > 0 ? ' '.$append : NULL)
                   );
    
    $out = array();
    $ret = NULL;
    $out = System::execute($cmd, NULL, NULL, $stdout, $stderr, NULL, $ret);
    
    if ($ret !== 0) {
      throw new Exception(sprintf("Fehler '%s' beim Ausführen des Befehls '%s'. Rückgabe: %d", $out, $cmd, $ret));
    }

    /* sonderfall für listfiles denn das gibt immer $ret = 0 zurück, toll, was? */
    if (!empty($stderr)) {
      throw new Exception(sprintf("Fehler '%s' beim Ausführen des Befehls '%s'.", $stderr, $cmd));
    }
    
    return $out;
  }
}
?>