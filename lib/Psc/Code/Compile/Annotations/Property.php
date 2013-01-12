<?php

namespace Psc\Code\Compile\Annotations;

/**
 * @Annotation
 *
 * @TODO die typen der Variablen müssen noch festgedengelt werden (im moment geht string für getter)
 */
class Property extends \Psc\Code\Annotation implements \Psc\Code\WriteableAnnotation {
  
  /**
   * @var string der (php-)Name des Properties welches compiliert werden soll
   */
  protected $name;
  
  //protected $setterName;
  //protected $getterName; //Yagni
  
  /**
   * Soll für das Property ein Setter ezeugt werden?
   * 
   * @var bool
   */
  protected $setter = TRUE;

  /**
   * Soll für das Property ein Getter ezeugt werden?
   * 
   * @var bool
   */
  protected $getter = TRUE;
  
  public function __construct(Array $parsedValues) {
    // ich stimme mal sowas von gar nicht mit doctrine überein, dass es nur public property injection und blöde array values injection geben soll!
    foreach ($parsedValues as $key => $value) {
      $this->callSetter($key,$value);
    }
  }
  
  /**
   * @return self
   */
  public static function create($name, $getter = TRUE, $setter = TRUE) {
    return new static(compact('name','getter','setter'));
  }
  
  /**
   * @return array
   */
  public function getWriteValues() {
    $values = array('name'=>$this->name);
    
    if ($this->getter === FALSE)
      $values['getter'] = FALSE;
      
    if ($this->setter === FALSE) {
      $values['setter'] = FALSE;
    }
    
    return $values;
  }
  
  /**
   * @param string $name
   * @chainable
   */
  public function setName($name) {
    $this->name = $name;
    return $this;
  }

  /**
   * @return string
   */
  public function getName() {
    return $this->name;
  }

  /**
   * @param bool $getter
   * @chainable
   */
  public function setGetter($getter) {
    $this->getter = $getter;
    return $this;
  }

  /**
   * @return bool
   */
  public function getGetter() {
    return $this->getter;
  }

  /**
   * @param bool $setter
   * @chainable
   */
  public function setSetter($setter) {
    $this->setter = $setter;
    return $this;
  }

  /**
   * @return bool
   */
  public function getSetter() {
    return $this->setter;
  }


}
?>