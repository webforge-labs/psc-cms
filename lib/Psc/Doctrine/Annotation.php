<?php

namespace Psc\Doctrine;

use Psc\Code\Generate\GClass;
use Psc\MissingPropertyException;

/**
 * Wrapper Klasse für Doctrine Annotations
 *
 * Gibt der Annotation ein Explizites Interface (wenn auch mit __magic)
 *
 * dies erlaubt aber intuitive dinge zu tun:
 * $manyToMany = $docBlock->getAnnotation('Doctrine\ORM\Mapping\ManyToMany);
 * $manyToMany->getTargetEntity();
 * $manyToMany->getMappedBy();
 *
 * $joinTable = Annotation::createDC('JoinTable', array('name'=>'game2sound',
 *                                           'joinColumns'=>array(
 *                                             Annotation::createDC('JoinColumn', array(
 *                                               'name'=>'game_id', 'onDelete'=>'cascade',
 *                                             ))
 *                                           ),
 *                                           'inverseJoinColumns'=>array(
 *                                             Annotation::createDC('JoinColumn', array(
 *                                               'name'=>'sound_id', 'onDelete'=>'cascade',
 *                                             ))
 *                                           )));
 */
class Annotation extends \Psc\Code\Annotation {
  
  /**
   * Die Annotation die gerade erstellt wird
   *
   * ist vom Typ: object<$this->annotationClass>
   */
  protected $annotation;
  
  /**
   * @var GClass
   */
  protected $annotationClass;
  
  public function __construct($annotationClass, Array $properties = array()) {
    $this->annotationClass = GClass::factory($annotationClass);
    $this->annotation = new $annotationClass($properties);
    $this->setProperties($properties);
  }
  
  public function setProperties(Array $properties) {
    foreach ($properties as $prop => $value) {
      $this->$prop = $value; // das mit magic benutzen zum validieren
    }
    
    return $this;
  }
  
  public function setValue($value) {
    $this->annotation->value = $value;
    return $this;
  }
  
  public function __set($field, $value) {
    if ($this->annotationClass->hasProperty($field)) {
      $this->annotation->$field = $value;
    } else {
      throw new MissingPropertyException($field, 'Annotation: '.$this->annotationClass->getFQN()." hat kein Feld '".$field."'");
    }
    return $this;
  }

  public function __get($field) {
    if ($this->annotationClass->hasProperty($field)) {
      return $this->annotation->$field;
    } else {
      throw new MissingPropertyException($field, 'Annotation: '.$this->annotationClass->getFQN()." hat kein Feld '".$field."'");
    }
    return $this;
  }
  
  public function __call($method, $args) {
    $prop = mb_strtolower(mb_substr($method,3,1)).mb_substr($method,4); // ucfirst in mb_string

    /* getter der nicht implementiert ist */
    if (mb_strpos($method, 'get') === 0) {
      
      if ($this->annotationClass->hasProperty($prop)) {
        return $this->annotation->$prop;
      } else {
        throw new MissingPropertyException($prop, 'Annotation: '.$this->annotationClass->getFQN()." hat kein Feld '".$prop."'");
      }
    }

    /* setter der nicht implementiert ist */
    if (mb_strpos($method, 'set') === 0) {

      if ($this->annotationClass->hasProperty($prop)) {
        $this->annotation->$prop = $args[0];
        return $this;
      } else {
        throw new MissingPropertyException($prop, 'Annotation: '.$this->annotationClass->getFQN()." hat kein Feld '".$prop."'");
      }
    }
  
    throw new \BadMethodCallException('Call to undefined method '.get_class($this).'::'.$method.'()');
  }
  
  /**
   * @return $this->annotationClass
   */
  public function unwrap() {
    return $this->annotation;
  }
  
  public static function create($class, Array $properties = array()) {
    return new static($class, $properties);
  }
  
  public static function createDC($shortName, Array $properties = array()) {
    return self::create('Doctrine\ORM\Mapping\\'.$shortName, $properties);
  }

  /**
   * Gibt alle Helpers für DoctrineAnnotations (oder andere) zurück
   *
   * Jeder Closure-Helper stellt eine Annotation dar und gibt für diese ein explizites interface:
   *
   * - $joinTable($name, $joinColumn(s), $inverseJoinColumn(s))
   *   für die columns kann ein array angegeben werden, muss aber nicht
   *
   * - $joinColumn($name, $referencedColumnName, $onDelete)
   *
   * - $manyToMany($targetEntity, $inversedBy($propertyName), Array $cascade, $fetch)
   * - $manyToMany($targetEntity, $mappedBy($propertyName), Array $cascade, $fetch)
   * - $oneToMany($targetEntity, $mappedBy($propertyName), Array $cascade, $fetch)
   * - $ManyToOne($targetEntity, $inversedBy($propertyName), Array $cascade, $fetch)
   *
   * - $inversedBy($propertyName)
   * - $mappedBy($propertyName)
   *
   * - $doctrineAnnotation($name)
   *   erzeugt eine Doctrine\ORM\Mapping\$name - Psc\Doctrine\Annotation
   * 
   * @return Array
   */
  public static function getClosureHelpers() {
    // constructor
    $doctrineAnnotation = function ($shortName, Array $properties = array()) {
      return \Psc\Doctrine\Annotation::createDC($shortName)->setProperties($properties);
    };
    
    // others
    $joinColumn = function ($name, $referencedColumnName = 'id', $onDelete = NULL) use ($doctrineAnnotation) {
      $properties = compact('name','referencedColumnName','onDelete');
      return $doctrineAnnotation('joinColumn', $properties);
    };
    
    $joinTable = function ($name, $joinColumns = array(), $inverseJoinColumns = array()) use ($doctrineAnnotation) {
      if (!is_array($joinColumns)) $joinColumns = array($joinColumns);
      if (!is_array($inverseJoinColumns)) $inverseJoinColumns = array($inverseJoinColumns);
      
      $properties = compact('name','joinColumns','inverseJoinColumns');
      
      return $doctrineAnnotation('joinTable', $properties);
    };
    
    // Relations
    $manyToMany = function ($targetEntity, Array $inversedOrMapped = array(), Array $cascade = NULL, $fetch = 'LAZY') use ($doctrineAnnotation) {
      $properties = array_merge(compact('targetEntity','cascade','fetch'), $inversedOrMapped);
      // inversedOrMapped fügt dann inversedBy oder mappedBy den Properties hinzu
      
      return $doctrineAnnotation('ManyToMany', $properties);
    };

    $oneToMany = function ($targetEntity, Array $mapped = array(), Array $cascade = NULL, $fetch = 'LAZY') use ($doctrineAnnotation) {
      $properties = array_merge(compact('targetEntity','cascade','fetch'), $mapped);
      
      return $doctrineAnnotation('OneToMany', $properties);
    };

    $manyToOne = function ($targetEntity, Array $inversed = array(), Array $cascade = NULL, $fetch = 'LAZY') use ($doctrineAnnotation) {
      $properties = array_merge(compact('targetEntity','cascade','fetch'), $inversed);
      
      return $doctrineAnnotation('ManyToOne', $properties);
    };

    $oneToOne = function ($targetEntity, Array $inversedOrMapped = array(), Array $cascade = NULL, $fetch = 'LAZY') use ($doctrineAnnotation) {
      $properties = array_merge(compact('targetEntity','cascade','fetch'), $inversedOrMapped);
      
      return $doctrineAnnotation('OneToOne', $properties);
    };
    
    
    // HelpersHelpers
    $inversedBy = function($propertyName) {
      return array('inversedBy'=>$propertyName);
    };

    $mappedBy = function($propertyName) {
      return array('mappedBy'=>$propertyName);
    };
    
    return compact('inversedBy','mappedBy','oneToOne', 'manyToMany','manyToOne','oneToMany','joinTable','joinColumn');
  }
  
  /**
   * @return GClass
   */
  public function getAnnotationClass() {
    return $this->annotationClass;
  }
  
  /**
   * @return string
   */
  public function getAnnotationName() {
    return $this->getAnnotationClass()->getFQN();
  }

  public function getInnerAnnotation() {
    return $this->unwrap();
  }
}
?>