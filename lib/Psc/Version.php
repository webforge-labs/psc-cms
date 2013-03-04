<?php

namespace Psc;

use Webforge\Common\String AS S;
use Psc\CMS\Configuration;

class Version {
  
  protected $release;
  
  public function __construct(Configuration $configuration, $library = 'psc-cms') {
    $this->release = $configuration->get(array($library,'version')) ?: '0.1-DEV';
  }
  
  public function __toString() {
    return (string) $this->release;
  }
  
  /**
   * Überprüft die Version anhand einer anderen Version
   * 
   * $version->is('>=','0.1') // gibt true zurück wenn version z.b. 0.1.1 oder 0.2 ist oder 0.1 ist
   */
  public function is($operator,$version) {
    return version_compare($this->release, $version, $operator);
  }  
  
  /**
   * Scannt ein Verzeichnis nach einem Dateipattern und
   * vergleicht dabei die Versionen um die aktuellste Version dieser Datei zurückgeben zu können
   *
   * Als Beispiel:
   * jquery-1.3.2.min.js
   * jquery-1.4.1.min.js
   * jquery-1.5.1.min.js
   * jquery-1.5.2.min.js
   *
   * gibt logischerweise jquery-1.5.2.min.js zurück
   *
   * @param string $directory das Verzeichnis (mit trailingsslash) welches durchsucht werden soll
   * @param string $search sollte der String am Anfang der Datei sein. Dies kann auch eine regexp sein (mit / umschlossen)
   * @param array $fileRegexps können zusätzliche reguläre Ausdrücke sein um aus einer Datei den Versionsstring herauszubekommen, diese werden benutzt, wenn das normale vorgehen (match ((?:[0-9]\.)+[0-9]) scheitert
   */
  public static function getLatestFileVersion($directory, $search, Array $fileRegexps = array()) {
    
    $standardRx = '/((?:[0-9]\.)+[0-9])/';
    
    $files = glob($directory.'*.*');
    $maxFile = NULL;
    $maxVersion = 0;
    $searchRx = S::startsWith($search,'/') ? $search : '/^'.$search.'/';
    foreach ($files as $file) {
      $fileName = basename($file);
      
      if (preg::match($fileName,$searchRx) > 0) {
      
        if (($version = preg::qmatch($fileName,$standardRx,1)) == '') {
          foreach ($fileRegexps as $fileRx) {
            if (($version = preg::qmatch($fileName, $fileRegexps)) != '') {
              break;
            }
          }
        }
        if ($version == '') {
          throw new Exception('Für Datei: "'.$fileName.'" konnte keine Version extrahiert werden');
        }
      
        if (version_compare($version, $maxVersion, '>')) {
          $maxVersion = $version;
          $maxFile = $file;
        }
      }
    }
    
    return array($maxFile,$maxVersion);
  } 
}
?>