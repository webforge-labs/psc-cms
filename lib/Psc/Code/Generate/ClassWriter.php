<?php

namespace Psc\Code\Generate;

use \Webforge\Common\System\File,
    \Psc\System\System,
    \Psc\System\ExecuteException,
    \Psc\TPL\TPL,
    \Webforge\Common\ArrayUtil AS A,
    Psc\Code\Code
  ;


/**
 * $writer = new ClassWriter();
 * $class = new GClass();
 * ...
 * $writer->setClass($class);
 * $writer->addImport(new GClass('Special\Class\In\Namespace'));
 * $writer->write(new \Webforge\Common\System\File('compiled.class.php'));
 *
 * @TODO die Möglichkeit in der Klasse Use Imports von Methoden + Properties etc zu bekommen
 */
class ClassWriter extends \Psc\Object {
  
  const USE_STYLE_LINES = 'lines';
  const USE_STYLE_BLOCK = 'block';
  
  const OVERWRITE = '__overwrite';
  
  protected $template =
'<?php

%namespace%
%use%
%class%
?>';
// wenn use wegfällt ist da stattdessen eine neue zeile
  
  /**
   * Die Klasse wird bei setClass() nach Imports durchwühlt
   *
   * zusätzlich kann man bei write noch welche hinzufügen
   */
  protected $foundImports = array();
  
  /**
   * Die Klasse die geschrieben werden sollen
   *
   * wird mit setClass() gesetzt und dabei analysiert
   *
   * @var GClass
   */
  protected $gClass;
  
  
  /**
   * @var string self::USE_STYLE_BLOCK|self::USE_STYLE_LINES
   */
  protected $useStyle = self::USE_STYLE_BLOCK;
  
  public function __construct(GClass $gClass = NULL, ClassReader $reader = NULL) {
    if (isset($gClass)) {
      $this->setClass($gClass, $reader);
      $this->setUseStyle(self::USE_STYLE_BLOCK); // weil der constructor neu ist, benutzen wir auch den neuen style
    }
  }
  
  
  public static function factory() {
    return new static();
  }
  
  /**
   * Setzt die Klasse des Writers die geschrieben werden soll
   * 
   * @param $reader wird dieser mit angegeben werden die Imports aus dem Reader importiert
   */
  public function setClass(GClass $gClass, ClassReader $reader = NULL) {
    $this->gClass = $gClass;
    $this->initImports($this->gClass, $reader);
    return $this;
  }
  
  public function setClassFromReader(ClassReader $reader) {
    return $this->setClass($reader->getClass(), $reader);
  }
  
  /**
   *
   * wenn man alle use klassen resetten will muss man foundImports nach setClass() zurücksetzen
   * @param GClass[] $imports ein Array von GClasses die zusätzlich (!) im USE benutzt werden sollen
   * @param $overwrite wenn dies == self::OVERWRITE ist wird die datei ohne exception überschrieben
   */
  public function write(File $file, Array $imports = array(), $overwrite = FALSE) {
    $php = $this->generatePHP($imports);
    
    if ($file->exists()) {
      if ($overwrite !== self::OVERWRITE) {
        $e = new ClassWritingException('Um eine nichtleere Datei zu überschreiben muss overwrite == self::OVERWRITE sein. Es wurde versucht '.$file.' zu schreiben',
                    ClassWritingException::OVERWRITE_NOT_SET);
        $e->writingFile = $file;
        throw $e;
      }
    } else {
      $file->getDirectory()->create(); // ensure Directory Exists
    }
    
    $file->writeContents($php, File::EXCLUSIVE);
    return $this;
  }
  
  public function generatePHP(Array $imports = array()) {
    if (!isset($this->gClass)) {
      throw new ClassWritingException('Klasse nicht gesetzt. (zuerst setClass() dann write().');
    }
    
    $use = NULL;
    
    /* merge mit params */
    foreach ($imports as $iClass) {
      $alias = NULL;
      if (is_array($iClass)) {
        list($iClass, $alias) = $iClass;
      }
      
      if (!($iClass instanceof GClass)) {
        throw new \Psc\Exception('Imports können nur vom typ GClass sein');
      }
      
      $this->addImport($iClass, $alias);
    }
    
    if (count($this->foundImports) > 0) {
      
      $useImports = array();
      foreach ($this->foundImports as $alias => $gClass) {
        // wir filten nach den imports, die im selben Namespace sind und keinen besonderen Alias haben
        if ($alias !== $gClass->getClassName() || $gClass->getNamespace() !== $this->gClass->getNamespace()) {
          $useImports[$alias] = $gClass;
        }
      }
      
      /* Render PHP */
      $classAlias = function ($gClass, $alias) {
        if ($alias === $gClass->getClassName()) {
          return $gClass->getFQN();
        } else {
          return $gClass->getFQN().' AS '.$alias;
        }
      };

      if (count($useImports) > 0 ) {
        $use .= "\n"; // abstand zu namespace (neue Zeile)
        if ($this->useStyle === self::USE_STYLE_LINES) {
          $use .= A::joinc($useImports, "use %s;\n", $classAlias);
        } else {
          $use .= 'use '.A::implode($useImports, ",\n    ", $classAlias);
          $use .= ';';
          $use .= "\n"; // abstand zu class (neue Zeile)
        }
      }
    }

    $php = TPL::miniTemplate($this->template, Array(
      'class'=>$this->gClass->php(),
      'use'=>$use,
      'namespace'=>$this->gClass->getNamespace() != NULL ? 'namespace '.ltrim($this->gClass->getNamespace(),'\\').';' : NULL
    ));
  
    return $php;
  }
  
  /**
   * Versucht alle Klasen, die importiert werden müssen wenn die Klasse geschrieben würde zu ermitteln
   *
   * dies geht natürlich nicht immer komplett fehlerfrei (aber erstmal ein schöner Anfang)
   *
   * @param ClassReader $classReader wird der ClassReader übergeben, werden aus diesem die Imports übernommen
   * @return array
   */
  public function initImports(GClass $gClass, ClassReader $classReader = NULL) {
    /* Wo haben wir imports?
      - Funktions-Parameter als Hints
      - in Bodys von Funktionen (können wir nicht)
      - beim extend oder implement (es ist höchstwahrscheinlich, dass wenn wir ein interface implementieren wir da auch imports haben)
      
      - die GClass selbst hat in usedClasses einen Array von Schlüssel FQN und Wert array(GClass, alias)
    */
    
    /* classReader */
    if (isset($classReader)) {
      if (!$classReader->getClass()->equals($gClass)) {
        throw Exception::create("Die Klasse des ClassReaders ist eine andere als die angegebene! '%s' != '%s' ", $classReader->getClass()->getFQN(), $gClass->getFQN());
      }
      $this->foundImports = $classReader->readUseStatements();
    } else {
      // start empty
      $this->foundImports = array();
    }
    
    /* zuerst die Parameter */
    foreach ($gClass->getMethods() as $method) {
      foreach ($method->getParameters() as $parameter) {
        if (($hint = $parameter->getHint()) instanceof GClass) {
          $this->addImport($hint);
        }
      }
    }
    
    /* implement */
    foreach ($gClass->getInterfaces() as $interface) {
      $this->addImport($interface);
    }
    
    /* die Classes aus der GClass */
    foreach ($gClass->getUsedClasses() as $list) {
      list($class, $alias) = $list;

      $this->addImport($class, $alias);
    }
    
    return $this;
  }
  
  /**
   * @return bool
   * @throws Exception bei Parse Errors
   */
  public function syntaxCheck(File $file, $do = 'throw') {
    if (!$file->exists()) {
      throw new \RuntimeException('Datei '.$file.' existiert nicht. Syntax check nicht möglich');
    }
    
    $process = new \Psc\System\Console\Process(System::which('php').' -l -f '.escapeshellarg((string) $file));
    $exit = $process->run();
    
    if ($exit > 0 || $exit == -1) {
      if ($do === 'throw') 
        throw new SyntaxErrorException($process->getOutput());
      else
        return FALSE;
    } else {
      return TRUE;
    }
  }
  
  /**
   * Fügt dem Writer für die aktuelle Klasse einen weiteren Import hinzu
   * 
   * dies wird bei setClass() jedoch verworfen, also nach setClass aufrufen
   * wird kein Alias gesetzt wird der Klassename als Alias benutzt (dies entspricht dem Verhalten von PHP)
   */
  public function addImport(GClass $import, $alias = NULL) {
    $explicit = TRUE;
    if ($alias === NULL) {
      $explicit = FALSE;
      $alias = $import->getClassName();
    }
    
    if (array_key_exists($alias, $this->foundImports) && !$this->foundImports[$alias]->equals($import)) {
      throw new Exception(sprintf("Alias: '%s' wurde doppelt requested von: '%s' (%s) und '%s' ",
                                  $alias, $import->getFQN(), $explicit ? 'explizit' : 'implizit', $this->foundImports[$alias]->getFQN()
                                  ));
      
    }
    
    $this->foundImports[$alias] = $import;
    return $this;
  }
  
  public function setUseStyle($style) {
    Code::value($style, self::USE_STYLE_LINES, self::USE_STYLE_BLOCK);
    $this->useStyle = $style;
    return $this;
  }
}
?>