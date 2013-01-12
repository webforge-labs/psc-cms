<?php

namespace Psc;

use \Webforge\Common\System\Dir,
    \Webforge\Common\System\File,
    \Psc\PSC
;

/**
 * Der Unterschied zum ClassLoader vom AutoLoader ist, dass der ClassLoader den AutoLoader (als sprich die Library) braucht
 *
 * der ClassLoader sollte dafür verwendet werden eigene Klassen des Projektes oder Third-Party-Libraries zu Autoloaden
 * Für jeden Namespace sollte ein ClassLoader instanziiert werden
 *
 * $cl = new ClassLoader('tiptoi'); // nimmt automatisch base/src/tiptoi als Verzeichnis
 */
class ClassLoader {
  /**
   * Der Name des Namespaces
   *
   * @var string ohne Backslash am Ende, mit Backslash am Anfang
   */
  protected $namespace;
  
  /**
   * Der Pfad zu allen Klassen des Namespaces
   *
   * @var Webforge\Common\System\Dir
   */
  protected $classPath;

  /**
   * Dateien die schon geladen wurden
   * @var array
   */
  protected $files = array();
  
  /**
   * Der letzte Pfad nachdem gesucht wurde
   *
   * @var \Webforge\Common\System\File
   */
  protected $searchPath;
  
  /**
   * Wenn gesetzt wird eine defiziliere Fehlermeldung beim ClassLoading benutzt
   * 
   * @var bool
   */
  protected $debug = TRUE;
  
  /**
   * @param string $namespace ohne \ davor. Ohne \ am Ende. Er wird ohnehin als absolut angesehen
   */
  public function __construct($namespace, \Webforge\Common\System\Dir $dir = NULL) {
    $this->setNamespace($namespace);
    
    if ($dir == NULL) {
      // erster bs vom namespace weg (deshalb this->ns) und in bs in fs umsetzen
      $dir = PSC::get(PSC::PATH_SRC);
    }
    $this->classPath = $dir;
  }
  
  public function autoLoad($class) {
    /*
      fix für mac: so dass hier immer eine full qualified Class ankommt
      (zumindest eine mit nicht backslash anfangend)
    */
    $class = ltrim($class,'\\');
    
    /* Ist die Klasse in unserem Namespace? */
    if (mb_strpos($class,$this->namespace) !== 0)
      return FALSE;

    $this->searchPath = new File($this->classPath, // hat DS am Ende
                                 str_replace('\\', DIRECTORY_SEPARATOR, $class).'.php'
                                );
    
    if ($this->debug && !$this->searchPath->exists()) {
      $e = new ClassLoadException(sprintf("Klasse: '%s' konnte vom ClassLoader[%s] nicht unter:\n'%s'\ngefunden werden. ('%s' wurde durchsucht)",
                                          '\\'.$class, $this->namespace, $this->searchPath, $this->classPath));
      $e->class = '\\'.$class;
      $e->namespace = $this->namespace;
      $e->searchPath = (string) $this->searchPath;
      $e->classPath = (string) $this->classPath;
      
      throw $e;
    }
    
    require (string) $this->searchPath;
    
    return TRUE;
  }
  
  public function setNamespace($ns) {
    if (mb_strpos($ns,'\\') === 0)
      $ns = mb_substr($ns,1);
    
    if (String::endsWith($ns,'\\'))
      $ns = mb_substr($ns,0,-1);
    
    $this->namespace = $ns;
    return $this;
  }
  
  public function getNamespace() {
    return $this->namespace;
  }

  public function register() {
    if (function_exists('__autoload')) {
      throw new Exception('konservatives __autoload verhindert das Laden dieses ClassLoaders');
    }
    spl_autoload_register(array($this,'autoLoad'));
    return $this;
  }
}
?>