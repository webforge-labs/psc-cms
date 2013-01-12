<?php

namespace Psc\Hitch;

use Psc\Code\Generate\ClassWriter;
use Psc\Code\Generate\GClass;
use Psc\Code\Generate\GParameter;
use Psc\Code\Generate\GProperty;
use Psc\Code\Generate\GMethod;
use Psc\PSC;
use Webforge\Common\System\Dir;

class Generator extends \Psc\Object {

  const EXTENSION_LISTCOLLECTIONS = 'extension:ListCollections';
  const EXTENSION_JSONFIELDS = 'extension:JSONFields';
  
  protected $namespace;
  
  protected $baseClass;
  
  /**
   * @var array
   */
  protected $objects;
  
  /**
   * @var bool
   */
  protected $overwrite = FALSE;
  
  /**
   * @var array
   */
  protected $extensions = array();
  
  /**
   * @var array const=>ClassMethodName
   */
  protected $buildInExtensions = array(
    self::EXTENSION_LISTCOLLECTIONS=>'extendListCollections',
    self::EXTENSION_JSONFIELDS=>'extendJSONFields'
  );
  
  /**
   * @var Psc\Code\Generate\ClassWriter
   */
  protected $classWriter;
  
  /**
   * @var Psc\Hitch\Module
   */
  protected $module;
  
  public function __construct(ClassWriter $classWriter = NULL) {
    $this->baseClass = new GClass('Psc\XML\Object');
    $this->classWriter = $classWriter ?: new ClassWriter();
    $this->module = PSC::getProject()->getModule('Hitch');
    $this->setUp();
  }
  
  protected function setUp() {
  }
  
  /**
   * @return GClass
   */
  public function addObject($className, Array $properties) {
    $class = $this->expandClassName($className);
    $class->setParentClass($this->baseClass);
    
    foreach ($properties as $key => $property) {
      $ot = $property->getObjectType();
      if ($ot !== NULL) {
        $property->setObjectType(
          $this->expandClassName($ot)
        );
      }
    }
    
    $this->extend($class, $properties);
    $this->objects[$class->getName()] = (object) array(
      'class'=>$class,
      'properties'=>$properties
    );
    
    return $class;
  }
  
  public function generate() {
    $classes = array();
    
    foreach ($this->objects as $object) {
      $gClass = $object->class;
      $gClass->createDocBlock()->addSimpleAnnotation('Hitch\XmlObject');
      
      foreach ($object->properties as $hitchProp) {
        $property = $gClass->createProperty($hitchProp->getPHPName(),GProperty::MODIFIER_PROTECTED);        
        $property->setDocBlock($hitchProp->createDocBlock());
      }
      
      $file = $this->getGenerationFile($gClass);
      $this->classWriter->setClass($gClass);
      $this->classWriter->addImport(new GClass('Hitch\Mapping\Annotation'), 'Hitch'); // use Alias für Annotations
  
      try {        
        $overwrite = $this->overwrite ? ClassWriter::OVERWRITE : FALSE;
        $file->getDirectory()->create();
        $this->classWriter->write($file, array(), $overwrite);
      } catch (\Psc\Code\Generate\ClassWritingException $e) {
        $ex = new GeneratorOverwriteException('Datei: '.$file.' existiert bereits. Zum überschreiben muss $overwrite auf TRUE gesetzt werden',0,$e);
        $ex->entityFile = $file;
        throw $ex;
      }
      
      $classes[(string) $file] = $gClass;
    }
    return $classes;
  }
  
  public function getGenerationFile(GClass $class) {
    return $this->module->getProject()->getClassFile($class->getName());
  }
  
  /**
   * @return GClass
   */
  protected function expandClassName($className) {
    return $this->module->expandClassName($className, $this->namespace);
  }
  
  /**
   * Ruft alle Extensions für ein Objekt auf welches mit add() hinzugefügt wird
   *
   * 
   */
  protected function extend(GClass $class, Array $properties = array()) {
    foreach ($this->extensions as $extension) {
      if (is_string($extension) && array_key_exists($extension,$this->buildInExtensions)) {
        $f = $this->buildInExtensions[$extension];
        $this->$f($class,$properties);
      } elseif ($extension instanceof \Closure) {
        $extension($class, $properties);
      } else {
        call_user_func($extension, $class, $properties);
      }
    }
    return $this;
  }
  
  /**
   *
   * $callback = function (\Psc\Code\Generate\GClass $class, Array $properties);
   * $properties = array(new \Psc\Hitch\Property(), ...);
   * 
   * @param mixed $callback entweder closure oder ein php callback.
   */
  public function addExtension($callback) {
    $this->extensions[] = $callback;
    return $this;
  }
  
  public function setBaseClass(GClass $class) {
    $this->baseClass = $class;
    return $this;
  }
  
  
  /* Build In EXTENSIONS */
  
  /**
   * ListCollections Extension
   *
   * wenn diese Extension geladen wird, werden die Arrays die für Listen von Hitch übergeben
   * werden direkt in Doctrine\Common\ArrayCollection s umgewandelt
   * dies geschieht indem der Setter der Klasse hinzugefügt wird
   */
  protected function extendListCollections(GClass $class, Array $properties=array()) {
$setterCode = <<< 'PHP_SETTER'
if ($list instanceof \Doctrine\Common\Collections\ArrayCollection) {
  $this->%1$s = $list;
} else {
  $this->%1$s = new \Doctrine\Common\Collections\ArrayCollection((array) $list);
}
return $this;
PHP_SETTER;

    foreach ($properties as $property) {
      if ($property->getType() == Property::TYPE_LIST) {
        $class->createMethod('set'.$property->getGetterName(),
                             array(new GParameter('list')),
                             sprintf($setterCode, $property->getPHPName())
                            );
      }
    }
  }
  
  /**
   * JSONFields Extension
   *
   * die Extension schreibt die Meta Daten-Funktion getJSONFields
   */
  protected function extendJSONFields(GClass $class, Array $properties = array()) {
    /* wir fügen alle Properties in die getJSONFields Funktion ein */
    $fields = array();
    foreach ($properties as $property) {
      
      if ($property->getType() === Property::TYPE_LIST) {
        $fields[$property->getPHPName()] = 'Collection<JSON>';
      } else {
        $fields[$property->getPHPName()] = NULL;
      }
    }
    $body = 'return $this->jsonFields;';
    
    $class->createProperty('jsonFields', GProperty::MODIFIER_PROTECTED, $fields);
    $class->createMethod('getJSONFields', array(), $body);
    $class->createMethod('addJSONField', array(new GParameter('field'), new GParameter('value')),
'$this->jsonFields[$field] = $value;
return $this;');
  }
}
?>