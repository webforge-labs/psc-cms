<?php

namespace Psc\Data\Type;

use Psc\Code\Generate\GClass;
use Psc\Code\Code;

class ObjectType extends \Psc\Data\Type\Type implements ParameterHintedType, \Psc\Doctrine\ExportableType {
  
  /**
   * @var GClass
   */
  protected $class;
  
  public function __construct(GClass $class = NULL) {
    // alternativ könnten wir auch hier mit stdClass starten?
    if (isset($class))
      $this->setClass($class);
  }

  public function getName($context = self::CONTEXT_DEFAULT) {
    if ($context === self::CONTEXT_DEBUG)
      return isset($this->class) ? $this->class->getFQN() : 'Object';
      
    return parent::getName($context);
  }
  
  public function expandNamespace($namespace) {
    if (isset($this->class) && $this->class->getNamespace() === NULL)
      $this->class->setNameSpace($namespace);
    return $this;
  }
  
  /**
   * @return NULL|String
   */
  public function getClassFQN() {
    return isset($this->class) ? $this->class->getFQN() : NULL;
  }
  
  /**
   * @param Psc\Code\Generate\GClass $class
   * @chainable
   */
  public function setClass(GClass $class = NULL) {
    $this->class = $class;
    return $this;
  }

  /**
   * @return Psc\Code\Generate\GClass
   */
  public function getClass() {
    return $this->class;
  }
  
  public function getGClass() {
    return $this->class;
  }
  
  public function hasClass() {
    return isset($this->class);
  }
  
  /**
   * Gibt den internen PHPTypen des Types zurück
   *
   * ist in diesem falle auch der DocType
   * wenn die Klasse gesetzt ist, wird der Name der Klasse ohne \ Davor zurückgegeben
   */
  public function getPHPType() {
    return isset($this->class) ? $this->class->getFQN() : 'object';
  }
  
  /**
   * wenn die Klasse gesetzt ist, wird der Name der Klasse mit \ Davor zurückgegeben (only if namespaceContext is === NULL)
   */
  public function getPHPHint($namespaceContext = NULL) {
    if (isset($this->class)) {
      if (isset($namespaceContext)) {
        if (trim($this->class->getNamespace(),'\\') === trim($namespaceContext,'\\')) {
          return $this->class->getClassName();
        } else {
          return $this->class->getFQN();
        }
      }
      return $this->class->getName();
    }
    
    return 'object';
  }
  
  /**
   * @inheritdoc
   */
  public function getParameterHint($useFQN = TRUE) {
    if (isset($this->class)) {
      if ($useFQN) {
        return '\\'.$this->class->getFQN();
      } else {
        return $this->class->getClassName();
      }
    }
    
    return $useFQN ? '\\stdClass' : 'stdClass';
  }
  
  /**
   * @inheritdoc
   */
  public function getParameterHintImport() {
    return $this->class;
  }
  
  public function getDoctrineExportType() {
    return \Doctrine\DBAL\Types\Type::OBJECT;
  }

  public function implementsInterface($FQN) {
    if (!($FQN instanceof GClass)) {
      $FQN = new GClass($FQN);
    }
    return $this->getGClass()->hasInterface($FQN);
  }
}
?>