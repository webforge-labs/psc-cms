<?php

namespace Psc\Code\Generate;

use Webforge\Common\System\File;
//use Doctrine\Common\Annotations\PhpParser;
use Psc\Doctrine\PhpParser;
use Psc\Code\Generate\GClass;
use ReflectionClass;
use Psc\Code\Code;

/**
 * Das Analogon zum ClassWriter
 *
 * Liest eine GClass aus einer Datei aus. Anders als die Klasse mit Reflection zu laden (was wir aber auch tun), lesen wir aus der Date noch z. B. die "use"-imports aus, die in der Klasse stehen
 *
 * später mal können wir auch nach Kommentaren schauen, die nicht innerhalb von Methoden-Bodys oder CodeBlöcken stehen
 * Der ClassReader kann dann an den ClassWriter gegeben werden, der dann die Datei wieder schreibt
 *
 * @TODO auch interfaces müssen speziell geparsed werden (wie use statements) da diese nicht mit reflection getracked werden können, wo sie mit "implements" hinzugefügt wurden
 */
class ClassReader extends \Psc\SimpleObject {
  
  /**
   * @var Webforge\Common\System\File
   */
  protected $file;
  
  /**
   * @var Psc\Code\Generate\GClass
   */
  protected $gClass;
  
  public function __construct(File $file, GClass $gClass = NULL, PhpParser $phpParser = NULL) {
    $this->file = $file;
    $this->phpParser = $phpParser ?: new PhpParser();
    $this->gClass = $gClass;
  }
  
  public function readUseStatements() {
    // wir nehmen uns den Doctrine PHP Parser zur Hilfe (package: common)
    
    $dcUseStatements = $this->phpParser->parseClass($this->getReflectionClass());
    // ach manno, das sind alles lowercase names als Alias und man weiß nicht ob sie explizit sind oder nicht
    // das doch doof weil alle im phpParser von Doctrine private und final ist, geht da nix
    
    $useStatements = array();
    foreach ($dcUseStatements as $lowerAlias => $class) {
      if (mb_strtolower($cn = Code::getClassName($class)) !== $lowerAlias) { // ein expiziter Alias
        $useStatements[$lowerAlias] = new GClass($class); 
      } else {
        $useStatements[$cn] = new GClass($class); // $cn nicht strtolower!
      }
    }
    
    return $useStatements;
  }
  
  /**
   * @return ReflectionClass
   */
  public function getReflectionClass() {
    return new ReflectionClass($this->getClass()->getFQN());
  }
  
  /**
   * @return Psc\Code\Generate\GClass
   */
  public function getClass() {
    if (!isset($this->gClass)) {
      $this->gClass = $this->getGClassFromFile();
    }
    return $this->gClass;
  }
  
  /**
   * @return Webforge\Common\System\File
   */
  protected function getGClassFromFile() {
    // @TODO geht nicht für projektfremde Dateien
    $gClass = \Psc\PSC::getProject()->getClassFromFile($this->file);
    $gClass->setSrcFileName((string) $this->file); // damit wir wirklich fremde sourcen angeben können
    return $gClass;
  }
  
  public function getFile() {
    return $this->file;
  }
}
?>