<?php

namespace Psc\Code\Generate;

use stdClass;
use Doctrine\ORM\Mapping\Annotation AS DoctrineAnnotation; 
use Psc\Doctrine\Annotation AS DoctrineAnnotationWrapper; // das hier extended
use Psc\Code\Annotation AS PscAnnotation; // das hier
use ReflectionObject;
use Psc\Code\WriteableAnnotation;
use Webforge\Types\Type;

/**
 *
 * wir müssen uns in dieser Klasse mit 2 Typen von Annotations rumschlagen:
 *  - \Psc\Code\Annotation hier alias PscAnnotation
 *  - Doctrine\ORM\Mapping\Annotation alias DoctrineAnnotation
 *
 *  warum das so ist liegt daran, dass ich die DoctrineAnnotation-Implementierung nicht mag (public properties, constructorInjection usw)
 *  die WriteableAnnotation macht das Chaos dann perfekt.
 *  Diese ist einfach nur dafür da, dass die Annotation korrekt ihre Values exportiert - was wir bei Doctrine Dirty mit Reflection machen müssen (bis alle DoctrineException mal Meta-Daten haben, wer weis)
 */
class AnnotationWriter extends \Psc\Data\Walker {
  
  protected $annotationNamespaceAliases = array();
  
  protected $defaultAnnotationNamespace;
  
  /**
   * Annotation     ::= "@" AnnotationName ["(" [Values] ")"]
   *
   * AnnotationName ::= QualifiedName | SimpleName
   * QualifiedName  ::= NameSpacePart "\" {NameSpacePart "\"}* SimpleName
   * NameSpacePart  ::= identifier | null | false | true
   * SimpleName     ::= identifier | null | false | true
   *
   * @param PscAnnotation|DoctrineAnnotation $annotation
   */
  public function writeAnnotation($annotation) {
    $walkedValues = array();
    foreach ($this->extractValues($annotation) as $field=>$value) {
      $walkedValues[] = $this->walkField($field, $value, $this->inferType($value));
    }
    
    return '@'.$this->getAnnotationName($annotation).( count($walkedValues) > 0 ? '('.implode(', ',$walkedValues).')' : NULL);
  }
  
  /**
   * @param PscAnnotation|DoctrineAnnotation $annotation
   */
  public function getAnnotationName($annotation) {
    if ($annotation instanceof PscAnnotation) {
      $name = $annotation->getAnnotationName();
    } else {
      $name = \Psc\Code\Code::getClass($annotation);
    }
    
    if (isset($this->defaultAnnotationNamespace) && mb_strpos($name, $this->defaultAnnotationNamespace) === 0) {
      return mb_substr($name, mb_strlen($this->defaultAnnotationNamespace)+1);
    }
    
    foreach ($this->annotationNamespaceAliases as $alias => $namespace) {
      if (mb_strpos($name, $namespace) === 0) {
        return $alias.'\\'.mb_substr($name, mb_strlen($namespace)+1);
      }
    }
    
    return '\\'.$name;
  }
  
  public function extractValues($annotation) {
    if ($annotation instanceof WriteableAnnotation) {
      return $annotation->getWriteValues();
    
    } else {
      if ($annotation instanceof DoctrineAnnotationWrapper) {
        $annotation = $annotation->unwrap();
      }
      
      $Values = array();
      
      $refl = new ReflectionObject($annotation);
      foreach ($refl->getProperties() as $property) {
        // write value (a plainValue / plainValues without a key)
        $val = $property->getValue($annotation);
        if (($name = $property->getName()) === 'value' && $val !== NULL) {
          $Values[] = $val; // jetzt doch nicht flatten, z.B. für orderBy
          continue;
          //
          //// flat all values, so this is written as @annotation("v1","v2","v3")
          //// not @annotation({"v1","v2","v3"})
          //if (is_array($val)) { 
          //  foreach ($val as $pval) {
          //    $Values[] = $pval;
          //  }
          //} else {
          //  $Values[] = $val;
          //}
          //continue;
        }
        
        // skip writing values with default value (from class)
        $defaultProperties = $property->getDeclaringClass()->getDefaultProperties();
        if (array_key_exists($name, $defaultProperties) && $defaultProperties[$name] === $val) {
          continue;
        }
        
        $Values[$name] = $val;
      }
    }
    return $Values;
  }
  
  public function walk($value, Type $type) {
    if ($value instanceof DoctrineAnnotation || $value instanceof PscAnnotation) {
      return $this->writeAnnotation($value);
    }
    
    return parent::walk($value,$type);
  }

  
  public function decorateString($string) {
    return sprintf('"%s"', $string);
  }
  
  public function decorateBoolean($bool) {
    return $bool ? 'true' : 'false';
  }

  public function decorateArrayEntry($walkedEntry, $key) {
    if (is_string($key))
      return sprintf('"%s"=%s', $key, $walkedEntry);
    else  
      return $walkedEntry;
  }
  
  public function decorateArray($walkedEntries, $arrayType) {
    return '{'.implode(", ",$walkedEntries).'}';
  }
  
  public function decorateField($field, $walkedValue, $fieldType) {
    if (is_string($field))
      return sprintf("%s=%s",$field, $walkedValue);
    else
      return $walkedValue; // int key
  }

  public function setAnnotationNamespaceAlias($namespace, $alias) {
    $this->annotationNamespaceAliases[$alias] = $namespace;
    return $this;
  }
  
  public function getDefaultAnnotationNamespace() {
    return $this->defaultAnnotationNamespace;
  }
  
  public function setDefaultAnnotationNamespace($namespace) {
    $this->defaultAnnotationNamespace = $namespace;
    return $this;
  }
}
