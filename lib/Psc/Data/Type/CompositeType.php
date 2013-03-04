<?php

namespace Psc\Data\Type;

use OutOfBoundsException;

class CompositeType extends \Psc\Data\Type\Type {
  
  /**
   * @var Psc\Data\Type\Type[]
   */
  protected $components = array();
  
  /**
   * @var string
   */
  protected $phpHint;
  
  public function __construct() {
    $this->defineHint();
    parent::__construct(); 
  }

  public function getName($context = self::CONTEXT_DEFAULT) {
    if ($context === self::CONTEXT_DEBUG)
      return \Webforge\Common\ArrayUtil::implode($this->components, '|', function ($component) {
        return $component->getName(Type::CONTEXT_DEBUG);
      });
      
    return parent::getName($context);
  }
  
  /**
   * Sollte $this->phpHint setzen
   */
  protected function defineHint() {
    
  }
  
  /**
   * @param Psc\Data\Type\Type $componentType
   * @param Psc\Data\Type\Type $componentType, ...
   * 
   */
  public function setComponents() {
    $c = 1;
    $this->components = array(); // reset
    foreach (func_get_args() as $type) {
      if (!($type instanceof Type)) throw new \InvalidArgumentException('Argumente können nur Psc\Data\Type\Type sein. '.\Psc\Code\Code::varInfo($type).' given');
      $this->components[$c] = $type;
      $c++;
    }
    return $this;
  }
  
  public function addComponent(Type $type) {
    $this->components[count($this->components)+1] = $type;
    return $this;
  }
  
  /**
   * @return array
   */
  public function getComponents() {
    return $this->components;
  }
  
  /**
   * Gibt eine bestimmte Componente zurück
   * 
   * @param int $num 1-basierend.
   */
  public function getComponent($num) {
    $num = (int) $num;
    if (!array_key_exists($num,$this->components))
      throw new OutOfBoundsException(sprintf("Die Komponente %d existiert nicht im Type: '%s'",$num, $this->getName()));
    
    return $this->components[$num];
  }
  
  public function getPHPHint() {
    return $this->phpHint; 
  }
}
?>