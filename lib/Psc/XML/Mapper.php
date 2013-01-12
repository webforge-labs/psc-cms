<?php

namespace Psc\XML;

use \Psc\Code\Code,
    \stdClass
;

class Mapper extends \Psc\Object {
  
  protected $modifiers = array();
  protected $processors = array();
  
  protected $mappings = array();
  
  /**
   * @var \Psc\XML\Object
   */
  protected $xml;
  
  public function __construct() {
    $this->processor('value', array($this,'valueProcessor'));
    
    $this->registerStandardActions();
  }
  
  /**
   * Löst ein Mapping in einen Wert auf
   * 
   * @param string $id die id der Definition die mit def() erstellt wurde
   * @return mixed
   */
  public function map($id) {
    $def = clone $this->getDef($id);
    
    if ($def->isRelative) { // ein relativer Pfad bedeutet, dass wir zuerst den ersten Teil des Pfades auflösen müssen
      $rid = array_shift($def->path); // das geht schon weil wir oben def clonen
      $value = $this->map($rid);
    } else {
      $value = $this->xml; // clone? nich clone?
    }

    foreach ($def->chain as $action) {
      $m = 'call'.ucfirst($action->type);
      
      $value = $this->$m($action->name, $value, $def);
    }
    
    return $value;
  }
  
  /**
   *
   * Der Pfad muss vollständig sein, d.h. es dürfen keine Elemente ausgelassen werden
   * jeder / bedetet unterElement
   *
   * beginnt der Pfad nicht mit / wird davon ausgegangen, dass das erste XML Element ein Bezeichner ist und wird mit dessen Pfad (rekursiv) ersetzt
   *
   * jedem Chain wird automatisch ein valueProcessor vorangestellt, wenn der erste Eintag ein Modifier ist, oder der Chain leer ist
   * @param string $id der (Unique-)Bezeichner für das Mapping
   * @param string $path der Pfad zu den Rohdaten für $id getrennt mit / für XML Elemente
   * @param string[] $chain im Format 'modifier:$name' oder 'processor:$name'
   */
  public function def($id, $path, Array $chain = array()) {
    $path = rtrim($path,'/');
    
    $def = new stdClass();
    $def->id = $id;
    $def->isRoot = mb_strpos($path,'/') === 0;
    $def->isRelative = !$def->isRoot;
    $def->path = explode('/',ltrim($path,'/'));
    $def->originalPath = $path;

    $def->chain = array();
    foreach ($chain as $action) {
      list ($type, $name) = explode(':',$action);
      $m = 'has'.ucfirst($type);
      
      if ($this->$m($name)) {
        $def->chain[] = (object) compact('type','name');
      } else {
        throw new Exception('Mapper hat keinen '.$type.' mit dem Namen: '.$name);
      }
    }
    
    /* wenn es mit einem modifier los geht, müssen wir zuerst einen processor anfügen, der das xml in eine value mapped */
    if (count($def->chain) == 0 || count($def->chain) > 0 && $def->chain[0]->type == 'modifier') {
      array_unshift($def->chain, (object) array('type'=>'processor','name'=>'value'));
    }
    
    $this->mappings[$id] = $def;
  }
  

  /**
   * array
   *  $xmlId => $path " " ("m"|"p") ":" $name ["," ...]
   *
   * Beispiel:
   *  'price'=> 'immobilie/preise/mietpreis_pro_qm m:commafloat',
   */
  public function defMapping(Array $mapping) {  
    foreach ($mapping as $xmlId => $d) {
      if (mb_strpos($d,' ') !== FALSE) {
        list ($path, $chain) = preg_split("/\s/",$d,2);
        $chain = explode(',',$chain);
        $chain = array_map(function ($p) {
          $value = trim($p) != '' ? trim($p) : NULL;
          $value = str_replace(array('m:','p:'), array('modifier:','processor:'), $value);
          return $value;
        },$chain);
      } else {
        $path = trim($d);
        $chain = array();
      }
      $this->def($xmlId,$path,$chain);
    }
    return $this;
  }

  
  /**
   * Registriert einen Modifier für den Mapper
   *
   * $callback = function ($value, \stdClass $def)
   * modifiers bearbeiten die schon gemappte XML Value und geben diese zurück
   */
  public function modifier($name, $callback) {
    $this->modifiers[$name] = $callback;
    return $this;
  }
  
  /**
   * @return bool
   */
  public function hasModifier($name) {
    return array_key_exists($name,$this->modifiers);
  }
  
  /**
   * Registriert einen processor für den Mapper
   *
   * Processoren bearbeiten das XML direkt und dürfen auch direkt das Objekt modifizieren oder werte zurückgeben
   * $callback = function ($value, \stdClass $def)
   *
   * $value kann hier ein XML element sein (wenn der processor z. B. am Anfang des Chains steht), oder aber auch ein Basiswert
   */
  public function processor($name, $callback) {
    $this->processors[$name] = $callback;
    return $this;
  }
  
  public function hasProcessor($name) {
    return array_key_exists($name,$this->processors);
  }

  protected function callModifier($name, $value, stdClass $def) {
    $cb = $this->modifiers[$name];
    if ($cb instanceof \Closure) {
      return $cb($value,$def);
    } else {
      return call_user_func($cb, $value, $def);
    }
  }


  protected function callProcessor($name, $xml, stdClass $def) {
    $cb = $this->processors[$name];
    if ($cb instanceof \Closure) {
      return $cb($value,$def);
    } else {
      return call_user_func($cb, $xml, $def);
    }
  }


  protected function getDef($id) {
    if (!array_key_exists($id,$this->mappings)) {
      throw new Exception('Mapping: '.Code::varInfo($id).' existiert nicht. Vorher mit def() hinzufügen');
    }
    
    return $this->mappings[$id];
  }
  

  /**
   *
   *
   * ist der Pfad leer, wird das $xml zurückgegeben
   */
  protected function valueProcessor(\Psc\XML\Object $xml, stdClass $def) {
    $value = $xml;
    $currentPath = array('<root>');
    foreach ($def->path as $key => $elementName) {
      if (!($value instanceof \Psc\XML\Object)) {
        throw new Exception('Parse Error: '.implode('/',$currentPath).' löst nicht zu einem \Psc\XML\Object auf!.'."\n".'Fehlerhafter Pfad ist: "'.$def->originalPath.'" in Definition: '.$def->id);
      }
      
      try {
        $value = $value->getElement($elementName);
      } catch (\Psc\MissingPropertyException $e) {
        throw new Exception('Parse Error: "'.implode('/',$currentPath).'" hat kein property mit dem Namen: "'.$e->prop.'".'."\n".'Fehlerhafter Pfad ist: "'.$def->originalPath.'" in Definition: '.$def->id);
      }
      $currentPath[] = $elementName;
    }
    
    return $value;
  }
  
  protected function registerStandardActions() {
    $this->modifier('trimNull',function ($value) {
      if (is_string($value)) {
        $value = trim($value);
        if ($value === '')
          return NULL;
      }
      
      return $value;
    });
    
    $this->modifier('trim',function ($value) {
      if (is_string($value)) {
        return trim($value);
      }
      return $value;
    });

    $this->modifier('int',function($value) {
      return (int) $value;
    });
    
    $this->modifier('commafloat', function ($value) {
      return floatval($value);
    });
  }
}