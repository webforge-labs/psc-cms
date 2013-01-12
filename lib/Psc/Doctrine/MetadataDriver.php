<?php

/**
 *
 * In diesem Treiber überschreiben wir die getAllClassNames - Methode von Doctrine, da diese allen Code im class verzeichnis ausführt,
 * wenn man dieses angibt.
 * Dies gibt vorallem dann Terror, wenn man exceptions in der Klassendatei definiert. Wir laden deshalb nur die Klassen, die das DoctrineObject extenden
 * 
 */
namespace Psc\Doctrine;

use \Psc\PSC,
    \Psc\Config,
    \Webforge\Common\System\File
;

class MetadataDriver extends \Doctrine\ORM\Mapping\Driver\AnnotationDriver {
  
  /**
   *
   * wir könnten hier eigentlich auch direkt auf $_classNames operieren, aber das scheint mir etwas instabil
   * @var string[]
   */
  protected $classes;
  

  /**
   * @param AnnotationReader $reader duck-typed annotationreader
   * @param null $paths wird ignoriert!
   * @param array $classes ein Array von Strings die Klassennamen sind, die der Treiber laden will (müssen lazyloadable sein
   */
  public function __construct($reader, $paths = null, Array $classes = array()) {
    parent::__construct($reader);
    $this->classes = $classes;
  }
  
  /**
   * Anders als Doctrine machen wir hier nicht über jede Datei require_once. Wir lassen den AutoLoader die Klassen laden
   * 
   */
  public function getAllClassNames() {
    return $this->classes;
  }
  
  /**
   * @chainable
   */
  public function addClass($className) {
    $className = ltrim($className,'\\');
    $this->classes[$className] = $className;
    return $this;
  }
  
  /**
   * @chainable
   */
  public function removeClass($className) {
    $className = ltrim($className,'\\');
    if (array_key_exists($className,$this->classes)) {
      unset($this->classes[$className]);
    }
    return $this;
  }
}
?>