<?php

namespace Psc\Data;

use \Webforge\Common\System\Dir,
    \Webforge\Common\System\File,
    \Psc\PSC,
    \Psc\Code\Code
;

/**
 * alle $keys können entweder ein array sein, oder ein mit : getrennter string (: damit es kein crash mit .jpg gibt)
 * alle $keys müssen verzeichnis freundlich sein und nur strings am besten
 */
class FileCache implements Cache {
  
  /**
   * @var Webforge\Common\System\Dir
   */
  protected $directory;
  
  /**
   * Wenn TRUE geben alle Funktionen die Normalerweise die Datei-Inhalte zurückgeben \Webforge\Common\System\File zurück
   */
  protected $direct;
  
  /**
   *
   * jeder Schlüssel wird relativ zu einem Verzeichnis in $rootDir abgebildet
   * also
   * thumbnails:50:60:cropped:so98vm.jpg  würde dann im default-Fall (rootDir === NULL)
   * base/cache/thumbnails/50/60/cropped/
   * werden
   */
  public function __construct(Dir $rootDir = NULL, $direct = FALSE) {
    $this->directory = $rootDir ?: PSC::get(PSC::PATH_CACHE);
    $this->direct = (bool) $direct;
  }
  
  /**
   *
   * @param string|File $value entweder eine Datei die abgelegt werden soll (wird kopiert) oder der Inhalt der Datei
   * @chainable
   * @return Cache
   */
  public function store($key, $value) {
    $key = $this->getKey($key);
    $file = $this->getFile($key);
    
    if (!$this->validate($file)) {
      // new
      $file->getDirectory()->create();
    } // else: update
    
    if ($value instanceof \Webforge\Common\System\File) {
      $value->copy($file);
    } else {
      $file->writeContents($value, File::EXCLUSIVE);

      if (!$file->exists()) {
        throw new \Psc\Exception('Cannot write to file exclusively: '.$file.' does not exist');
      }
    }
    
    return $this;
  }
  
  /**
   * Gibt den Inhalt der Datei (direct = FALSE) oder die Datei zurück (direct = TRUE)
   *
   * 
   * @return string (binärdaten aus der Datei)
   */
  public function load($key, &$loaded) {
    $file = $this->getFile($this->getKey($key));
    if ($this->validate($file)) {
      $loaded = TRUE;
      
      return $this->direct ? $file : $file->getContents();
    }
    $loaded = FALSE;
  }
  
  /**
   * @return bool
   */
  public function hit($key) {
    $file = $this->getFile($this->getKey($key));
    return $this->validate($file);
  }
  
  /**
   * Entfernt die Dateia us dem Cache
   * @chainable
   */
  public function remove($key) {
    $file = $this->getFile($this->getKey($key));
    $file->delete();
    return $this;
  }
  
  /**
   * @return bool
   */
  protected function validate(File $file) {
    return $file->exists();
  }

  /**
   * @return File
   */
  protected function getFile(Array $key) {
    $key = array_filter($key, function ($part) {
      return !preg_match('%(/|\\\\|\.\.)%',$part);
    });
    $file = str_replace(array('/','\\','..'),'-',array_pop($key));
    
    /* allow storage in root? */
    $dir = clone $this->directory;
    if (count($key) > 0) {
      $dir->append(implode('/',$key).'/');
    
      if (!$dir->isSubdirectoryOf($this->directory)) {
        throw new \Psc\Exception('Fehlerhafter Cache key: '.Code::varInfo($key).' erzeugt einen Pfad, der kein Unterverzeichnis von '.$this->directory.' ist: '.$dir);
      }
    }
    
    return new File($dir, $file);
  }
  
  /**
   * @return Array
   */
  protected function getKey($key) {
    if (is_string($key) && mb_strpos($key,':') !== FALSE) {
      $key = explode(':',$key);
    }
    
    return (array) $key;
  }
}
?>