<?php

namespace Psc\PHPJS;

use Psc\Code\Code;
use Psc\Code\Callback;


/**
 *
 * Konvertiert PHP Objekte in JSON Strings und konvertiert sie hinterher (mit richtigen Typen) wieder zurück
 *
 * Problem:
 *  - Einmal in Json serialisierte Objekte kommen beim zurückkonvertierten (json_decode) immer als stdClass-Objekte zurück
 *  - man möchte nicht alle Klassenvariablen die serialisiert werden sollen public machen
 *          
 * Die "Magic" warum diese Klasse funktioniert ist das property $metadata. Deshalb sind unserialize und serialize() beide nicht statische Klassenmethoden, sondern Objektmethoden.
 * Möchte man also Daten serialisieren und später zurückhaben, muss man dasselbe Serializer-Objekt benutzen, oder die Metadaten wiederherstellen
 */
class Serializer extends \Psc\Object {
  
  protected $metadata = array();
  
  public function __construct($metadataJSON = NULL) {
    if (isset($metadataJSON)) {
      $this->initMetadata($metadataJSON);
    }
  }
  
  
  public function unserialize($jsonString, $rootClassName) {
    $jsonData = json_decode($jsonString);
    
    /* wir durchlaufen hier alles rekursiv und importieren (unexporten) */
    $data = $this->import($jsonData, $rootClassName);
    
    return $data;
  }
  
  public function import($jsonData, $class) {
    $data = array();
    
    if ($jsonData instanceof \stdClass) {
      $meta = (array_key_exists($class,$this->metadata)) ? $this->metadata[$class] : array();
      
      foreach ($jsonData as $field => $value) {
        if (isset($meta[$field])) {
          list($type,$param) = $meta[$field];
          switch ($type) {
            case 'stdClass':
              /* das müsste glaube ich zu stdClass casten,
               $value müsste aber eh schon stdClass sein,
               weil das der Default von json_decode ist */
              $data[$field] = (object) $value;
              break;
            
            case 'array':
              /* das castet das stdClass Objet von json_decode in einen array um,
                was funktioniert, das müssen wir hier machen, damits mit dem array weitergeht
              */
              $data[$field] = $this->import((array) $value,$param);
              break;
            
            case 'PHPJSObject':
              $subClass = $param;
              $data[$field] = $this->import($value, $subClass);
              if (!($data[$field] instanceof $subClass)) {
                throw new \Psc\Exception('Internal Error: subclass doesnt return instanceof '.$subclass);
              }
              break;
            
            default:
              $data[$field] = $value;
            
          }
        } else {
          throw new Exception('Fehlende Metadaten für: '.$class.'::$'.$field.'. Dies ist entweder ein interner Fehler, oder es wurden nicht die korrekten Metadaten für den Serializer geladen. Diese müssen identisch sein zu denen die bei serialize() des Objektes erzeugt wurden.');
        }
      }
      
    } elseif(is_array($jsonData)) {
      /* Rekursiver Aufruf aus dem main switch() */
      $meta = $class;
      foreach ($jsonData as $field => $value) { // bitte beachten sie hier, dass $field ein numerischer index ist (oder ein assoziativer schlüssel aus dem Array)
        if (isset($meta[$field])) {
          list($type,$param) = $meta[$field];
          switch ($type) {
              case 'stdClass':
              /* das müsste glaube ich zu stdClass casten,
               $value müsste aber eh schon stdClass sein,
               weil das der Default von json_decode ist */
              $data[$field] = (object) $value;
              break;
             
            case 'array':
              /* wir wandeln hier rekursiv arrays um, damit objekte in arrays auch korrekt serialisiert werden */
              $data[$field] = $this->import((array) $value, $param);
              break;
              
            case 'PHPJSObject':
              $subClass = $param;
              $data[$field] = $this->import($value, $subClass);
              break;
            
            case 'int':
              $data[$field] = (int) $value;
              break;
            
            case 'float':
              $data[$field] = (float) $value;
              break;
              
            default:
              $data[$field] = $value;
          }
        } else {
          throw new Exception('Fehlende Metadaten für: Array:$'.$field.'. Dies ist entweder ein interner Fehler, oder es wurden nicht die korrekten Metadaten für den Serializer geladen. Diese müssen identisch sein zu denen die bei serialize() des Objektes erzeugt wurden.');
        }
      }
      return $data; // wir geben hier zurück an den case in der mitte
    }

    $callback = new Callback($class, 'factoryJSData');
    return $callback->call(array($data, $class));
  }
  
  public function serialize(Object $object) {
    return json_encode($this->export($object));
  }
  
  /**
   *
   * schreibt Metadaten über das Objekt in $metadata
   * @return mixed eine Datenstruktur die durch json_encode einfach encodiert werden kann
   */
  public function export($data, &$reference = NULL) {
    $export = array();
    $jscode = array();
    
    if ($data instanceof Object) {
      $object = $data;
      $fields = $object->getJSFields();
    
      if (!is_array($fields))
        throw new Exception('getJSDataFields nicht korrekt implementiert (muss array zurückgeben) '.Code::getInfo($fields));
      
      $class = Code::getClass($object);
      foreach ($fields as $field) {
        if (is_string($field)) {
          $value = $object->__get($field);
        }
      
        if (is_object($value)) {
          if ($value instanceof Object) {
            $this->metadata[$class][$field] = array('PHPJSObject',Code::getClass($value));
            $value = $this->export($value);
          } elseif($value instanceof stdClass) {
            $this->metadata[$class][$field] = array('object','stdClass');
          } else {
            throw new Exception('Es ist nicht möglich andere Objekte außer stdClass und PHPJSObject zu exportieren/serialisieren. Objektklasse: '.$class.' kann nicht serialisiert werden');
          }
        } elseif (is_array($value)) {
          /* Array innerhalb des Objektes, kann natürlich weitere Objekte enthalten, diese müssen wir auch exportieren */
          $this->metadata[$class][$field] = array('array',array());
          $value = $this->export($value, $this->metadata[$class][$field][1]);
        } else {
          $this->metadata[$class][$field] = array(Code::getType($value),NULL);
        }
      
        $export[$field] = $value;
      }
    } elseif(is_array($data)) {
    /* rekursiver Aufruf aus der Mitte */
      foreach ($data as $field => $value) {
        
        if (is_object($value)) {
          if ($value instanceof Object) {
            $reference[$field] = array('PHPJSObject',Code::getClass($value));
            $value = $this->export($value);
          } elseif($value instanceof stdClass) {
            $reference[$field] = array('object','stdClass');
          } else {
            throw new Exception('Es ist nicht möglich andere Objekte außer stdClass und PHPJSObject zu exportieren/serialisieren. Objektklasse: '.$class.' kann nicht serialisiert werden');
          }
        } elseif (is_array($value)) {
          /* Array innerhalb des Objektes, kann natürlich weitere Objekte enthalten, diese müssen wir auch exportieren */
          $reference[$field] = array('array',array());
          $value = $this->export($value, $reference[$field][1]);
        } else {
          $reference[$field] = array(Code::getType($value),NULL);
        }
      
        $export[$field] = $value;
      }
    }
    
    return $export;
  }
  
  /**
   * @return string
   */
  public function getMetadataJSON() {
    return json_encode($this->metadata);
  }
  
  /**
   * @param string|array $meta kann entweder ein Metadata Array sein oder ein json welcher durch getMetadataJSON() exportiert wurde
   */
  public function initMetadata($meta) {
    if (is_string($meta)) {
      $this->metadata = json_decode($meta, TRUE); // decode in assoc
    } elseif(is_array($meta)) {
      $this->metadata = $meta;
    } else {
      throw new Exception('Unbekanntes Format für Metadata');
    }
    return $this;
  }
  
  public function setMetadata($meta) {
    return $this->initMetadata($meta);
  }
}

class Exception extends \Psc\Exception {}