<?php

namespace Psc\Hitch;

use Psc\Code\Code;
use Psc\Code\Generate\GClass;
use Psc\Code\Generate\DocBlock;

class Property extends \Psc\Object {
  
  const TYPE_ELEMENT = 'element';
  const TYPE_ATTRIBUTE = 'attribute';
  const TYPE_VALUE = 'value';
  const TYPE_LIST = 'list';
  
  /**
   * Das ist nicht der Hitch-Type
   * 
   * @var const TYPE_*
   */
  protected $type;
  
  /**
   * Der Hitch Type
   *
   * die Klasse des UnterObjektes
   * wenn string wird angenommen, dass es die baseClass des Generators ableitet und im Namespace des Generators liegt
   *
   * @var GClass|string|NULL
   */
  protected $objectType;
  
  protected $phpName;
  
  protected $xmlName;
  
  /**
   * @param string $name der Name des Properties des Objektes (nicht im XML)
   * @param const TYPE_*
   */
  public function __construct($name,$type) {
    $this->setType($type);
    $this->phpName = $name;
    $this->xmlName = $name;
  }
  
  public static function create($name, $type) {
    return new static($name,$type);
  }
  
  /**
   * Gibt den passenden Hitch-Doc-Block für das Property zurück
   * 
   * @return DocBlock
   */
  public function createDocBlock() {
    $docBlock = new DocBlock(NULL);
    
    $attributes = array();
    $attributes['name'] = $this->xmlName;
    
    if (isset($this->objectType)) {
      $attributes['type'] = ltrim($this->objectType instanceof GClass ? $this->objectType->getFQN() : (string) $this->objectType, '\\');
    }
    
    if ($this->type === self::TYPE_ELEMENT) {
      $annotation = 'Hitch\XmlElement';
      
    } elseif ($this->type === self::TYPE_LIST) {
      $annotation = 'Hitch\XmlList';
      
      if (isset($this->wrapper)) {
        $attributes['wrapper'] = $this->wrapper;
      }
      
    } elseif ($this->type === self::TYPE_LIST) {
      $annotation = 'Hitch\XmlValue';
    
    } elseif ($this->type === self::TYPE_ATTRIBUTE) {
      $annotation = 'Hitch\XmlAttribute';

    } elseif ($this->type === self::TYPE_VALUE) {
      $annotation = 'Hitch\XmlValue';
      unset($attributes['name']);
      
    } else {
      throw new \Psc\Exception('Unbekannter Type: '.Code::varInfo($this->type));
    }
    
    $docBlock->addSimpleAnnotation($annotation, $attributes);
    return $docBlock;
  }
  
  public function setType($type) {
    $this->type = Code::dvalue($type, self::TYPE_ELEMENT, self::TYPE_ATTRIBUTE, self::TYPE_VALUE, self::TYPE_LIST);
    return $this;
  }
  
  public function setObjectType($class) {
    //if (is_string($class)) $class = new GClass($class);
    
    $this->objectType = $class;
    return $this;
  }
  
  public function setWrapper($wrapperElementName) {
    if ($this->type !== self::TYPE_LIST)
      throw new \Psc\Exception('setWrapper() ist nur für Properties des Typs LIST möglich.');
    
    $this->wrapper = $wrapperElementName;
    return $this;
  }
  
  public function setXMLName($xmlname) {
    $this->xmlName = $xmlname;
    return $this;
  }
  
  public function getName() {
    return $this->phpName;
  }
  
  public function getGetterName() {
    return ucfirst($this->phpName);
  }
  
  public function getPHPName() {
    return $this->phpName;
  }
}
?>