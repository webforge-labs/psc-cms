<?php

namespace Psc\Code\Compile;

use Psc\Code\Generate\GClass;
use Psc\Code\Generate\ClassWriter;
use Psc\Code\Generate\ClassReader;
use Webforge\Common\System\File;
use Webforge\Common\System\Directory;

/**
 * Der Compiler führt eine GClass mit bestimmten Annotations in eine neue GClass über
 * 
 * Compiling einer Klasse kann viele Formen besitzen.
 * Es könnten automatisch Getter / Setter erzeugt werden
 * Annotations eingefügt werden
 * Methoden oder ganze Interfaces automatisch implementiert werden
 * Methoden ergänzt oder umgeschrieben werden
 * Daten hinzugefügt oder entfernt werden
 * 
 * Es gibt keine Beschränkungen, dass eine noch nicht kompilierte Klasse auch lauffähig sein muss. Die "Rule of thumb" Regel für eine Funktion die kompiliert werden soll, wäre, wenn man eine Aufgabe erledigen muss, die Sinn machen würde stark zu abstrahieren - durch Objekte oder Modelle oder weiteres, aber nicht aus Performance Gründen sauber abstrahiert werden darf.
 * Da man quasi zur Compile-Zeit ein großes Kontingent an Zeit zur Verfügung hat, muss man komplexe Berechnungen und Code-Erzeugungen nicht scheuen und kann dadurch höchst einfachen + schnellen Code in der kompilierten Klasse benutzen.
 * 
 * Diese Klasse ist mustergültig eingerückt - weil sie ein "Fixture" für den CompilerTest ist
 * 
 * @TODO imports aus der OriginalKlasse müssen geparsed werden und beibehalten werden
 *       done: aber: Alias wird nicht case-sensitiv importiert
 */
class Compiler extends \Psc\SimpleObject {
  
  /**
   * @var Psc\Code\Generate\ClassWriter
   */
  protected $classWriter;
  
  /**
   * @var Psc\Code\Generate\ClassReader
   */
  protected $classReader;
  
  /**
   * Die input GClass
   * 
   * @var Psc\Code\Generate\GClass
   */
  protected $gClass;
  
  /**
   * Die (kompilierte) output GClass
   * 
   * @var Psc\Code\Generate\GClass
   */
  protected $cClass;
  
  /**
   * @var Extension[]
   */
  protected $extensions = array();
  
  public function __construct(ClassReader $classReader, ClassWriter $classWriter = NULL) {
    $this->classWriter = $classWriter ?: new ClassWriter();
    $this->classReader = $classReader;
    $this->gClass = $this->classReader->getClass();
    
    $this->setUp();
  }
  
  public function initDefaultExtensions() {
    $this->addExtension(new MarkCompiledExtension());
  }
  
  public function compile(File $out, $flags = 0) { // 0x000000 geht leider noch nicht
    $this->cClass = clone $this->gClass; // mit leerer oder mit clone anfangen?
    
    $this->processExtensions();

    $this->classWriter->setClass($this->cClass, $this->classReader);
    $this->classWriter->write($out, array(), ClassWriter::OVERWRITE);
    $this->classWriter->syntaxCheck($out);
    
    return $this;
  }
  
  protected function processExtensions($flags = 0) {
    foreach ($this->extensions as $extension) {
      $extension->compile($this->cClass, $flags);
    }
  }
  
  public function setUp() {
    $this->classWriter->setUseStyle(ClassWriter::USE_STYLE_LINES); // "use" für jeden import davorschreiben
  }
  
  public function addExtension(Extension $extension) {
    $this->extensions[] = $extension;
    return $this;
  }
  
  public function getExtensions() {
    return $this->extensions;
  }
  
  public function getGClass() {
    return $this->gClass;
  }
}
?>