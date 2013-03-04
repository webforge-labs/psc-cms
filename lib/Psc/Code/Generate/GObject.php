<?php

namespace Psc\Code\Generate;

use Psc\Code\Code,
    \Reflector,
    Webforge\Common\System\File,
    Psc\Object,
    Psc\A
;

/**
 * @todo wenn im Objekt der DocBlock schon gesetzt ist, wird der mit create() einfach überschrieben
 */
abstract class GObject extends \Psc\Object {
  
  protected $reflector;
  
  /**
   * @var Psc\Code\DocBlock
   */
  protected $docBlock;
  
  protected $elevated = FALSE;
  
  protected $codeWriter;
  
  public function __construct() {
    $this->setUp();
  }
  
  /**
   * @chainable
   */
  abstract public function elevate(Reflector $reflector);
  
  public function isElevated() {
    return $this->elevated;
  }
  
  /**
   * @param string|array $prop1
   * @param string|array $prop2,...
   */
  protected function elevateValues() {
    $properties = func_get_args();
    foreach ($properties as $prop) {
      
      /* expand */
      if (is_array($prop)) {
        list($prop, $getter) = $prop;
      } else {
        $getter = 'get'.ucfirst($prop);
      }
      
      $this->$prop = $this->reflector->$getter();
    }
  }
  
  protected function exportArgumentValue($value) {
    return $this->exportMixedValue($value);
  }

  protected function exportPropertyValue($value) {
    return $this->exportMixedValue($value);
  }
  
  protected function exportMixedValue($value) {
    if (is_array($value) && A::getType($value) === 'numeric') {
      return $this->getCodeWriter()->exportList($value);
    } elseif (is_array($value)) {
      return $this->getCodeWriter()->exportKeyList($value);
    } else {
      try {
        return $this->getCodeWriter()->exportBaseTypeValue($value);
      } catch (\Psc\Code\Generate\BadExportTypeException $e) {
        throw new \RuntimeException('In Argumenten oder Properties können nur Skalare DefaultValues stehen. Die value muss im Constructor stehen.', 0, $e);
      }
    }
  }
  
  protected function setUp() {
  }
  
  public function setModifier($modifier, $status = TRUE) {
    if ($status == TRUE) {
      $this->modifiers |= $modifier;
    } else {
      $this->modifiers &= ~$modifier;
    }
    return $this;
  }

  public static function reflectorFactory(Reflector $reflector) {
    $g = new static();
    $g->elevate($reflector);
    return $g;
  }

  /**
   * @param int $startLine 1-basierend
   */
  public function getSourceCode(File $file, $startLine, $endLine) {
    $extracter = new \Psc\Code\Extracter();
    
    if (!$file->isReadable()) {
      throw new \Psc\Exception('Body der Funktion/Methode '.$this->getName().' kann nicht ermittelt werden, da "'.$file.'" nicht lesbar ist.');
    }

    return $extracter->extract($file->getContents(), $startLine, $endLine);
  }
  
  public static function factory() {
    $args = func_get_args();
    return Object::factory(get_called_class(), $args, Object::REPLACE_ARGS);
  }
  
  /**
   * @param Psc\Code\Generate\DocBlock $docBlock
   * @chainable
   */
  public function setDocBlock(DocBlock $docBlock) {
    $this->docBlock = $docBlock;
    return $this;
  }
  
  public function createDocBlock($body = NULL) {
    $block = new DocBlock($body);
    $this->setDocBlock($block);
    return $block;
  }
  
  public function elevateDocBlock(Reflector $reflector) {
    $dc = $reflector->getDocComment();
    if ($dc != '') {
      return $this->createDocBlock($dc);
    }
  }

  /**
   * @return Psc\Code\Generate\DocBlock|NULL
   */
  public function getDocBlock($autoCreate = FALSE) {
    if ($autoCreate && !$this->hasDocBlock())
      $this->createDocBlock();
    
    return $this->docBlock;
  }
  
  public function hasDocBlock() {
    return $this->docBlock != NULL;
  }
  
  public function phpDocBlock($baseIndent = 0) {
    if (isset($this->docBlock)) {
      return \Webforge\Common\String::indent($this->docBlock->toString(),$baseIndent);
    }
  }
  
  public function getCodeWriter() {
    if (!isset($this->codeWriter))
      $this->codeWriter = new CodeWriter();
    
    return $this->codeWriter;
  }
}
?>