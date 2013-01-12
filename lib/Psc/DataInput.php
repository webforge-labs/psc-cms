<?php

namespace Psc;

use InvalidArgumentException,
    Psc\Code\Code
;

/**
 * Wrapper um $_POST und $_GET herum
 *
 * Setzt die Variablen um und transformierte leere in NULL usw
 * 
 */
class DataInput extends \Psc\Object {
  
  const THROW_EXCEPTION = 'throw';
  const RETURN_NULL = 'return_null';
  
  const TYPE_ARRAY = 'array';
  const TYPE_OBJECT = 'object';
  
  
  /**
   * @var const
   */
  protected $type;
  
  
  /**
   * @var array|stdClass 
   */
  public $data;
  
  
  public function __construct(&$data = NULL, $type = self::TYPE_ARRAY) {
    if ($type == self::TYPE_ARRAY) {
      if (!isset($data) || !is_array($data)) {
        $this->data = array();
      } else {
        $this->data =& $data;
      }
    } else {
      throw new Exception('das gibt es noch nicht');
    }
    $this->type = $type;
  }
  
  
  /**
   * Gibt einen Pfad zu einem Wert aus dem Array zurück
   * 
   * Ableitenden Klassen können dann sowas machen:
   *
   * public function bla() {
   *  $keys = func_get_args();
   *  return $this->getDataWithKeys($keys, self::THROW_EXCEPTION);
   * }
   *
   * oder so
   *
   * kann keine Defaults sondern nur return NULL oder eine Exception schmeissen
   */
  public function getDataWithKeys(Array $keys, $do = self::RETURN_NULL) {
    $data = $this->data;
  
    foreach ($keys as $key) {
      if (is_array($key)) {
        throw new InvalidArgumentException('$keys kann nur ein Array von Strings sein. '.Code::varInfo($keys));
      }
      
      if (!is_array($data) || !array_key_exists($key,$data)) {
        
        if ($do === self::RETURN_NULL) {
          return NULL;
        } else {
          $e = new DataInputException('Konnte Schlüssel: "'.implode('.',$keys).'" nicht finden');
          $e->keys = $keys;
          throw $e;
        }
      }
      
      $data = $data[$key];
    }
    
    if (is_array($data))
      return $this->cleanArray($data);
    else 
      return $this->clean($data);
  }


  /**
   * Setzt einen Wert mit einem Pfad
   * 
   * Ableitenden Klassen können dann sowas machen:
   *
   * public function bla() {
   *  $keys = func_get_args();
   *  $value = array_pop($keys);
   *  return $this->setDataWithKeys($keys, $value, self::THROW_EXCEPTION);
   * }
   *
   * oder so
   * ist $keys ein leerer array werden alle Daten des Objektes mit $value ersetzt.
   * ist $value dann kein Array wird toArray() einen array mit $value als einziges Element zurückgeben - das ist sehr weird
   * also besser ist immer $value einen array zu haben
   */
  public function setDataWithKeys(Array $keys, $value) {
    $data =& $this->data;
    
    if ($keys === array()) {
      if (!is_array($value)) {
        throw new DataInputException('Wenn $keys leer ist, darf value nur ein array sein!');
      }
      
      return $data = $value;
    }
    
    $lastKey = array_pop($keys);
    foreach ($keys as $key) {
      if (!array_key_exists($key,$data)) {
        $data[$key] = array();
      }
      $data =& $data[$key];
    }
    $data[$lastKey] = $value;
    
    return $data;
  }
  
  protected function clean($value) {
    if(is_string($value) && trim($value) == '') {
      $value = NULL;
    }
    return $value;
  }
  
  protected function cleanArray(Array $data) {
    foreach ($data as $key => $value) {
      if (is_array($value)) {
        $data[$key] = $this->cleanArray($value);
      } else {
        $data[$key] = $this->clean($value);
      }
    }
    return $data;
  }

  /**
   * Gibt DATA mit gecleanten Werten zurück
   * 
   * @return array
   */
  public function getData() {
    $data = $this->data;
    return $this->cleanArray($data);
    
    /* array map geht nicht weil closure nicht $this kann - doh */
  }
  
  /**
   * Gibt einen Wert aus den Daten zurück
   *
   * Der dritte Parameter ist anfangs etwas verwirred, er ist aber dafür da ein fine-grained control über diese Klase zu bekommen
   *
   * Der Standardfall den man aus einem Formular machen möchte ist z. B.:
   * $ids = $formDataPOST->get(array('data','ids'), array());
   *
   * was hier passiert ist, dass wenn $_POST['data']['ids'] nicht gesetzt ist. Also der array in $_POST['data'] keinen schlüssel ids hat, ein array() zurückgibt (also den 2ten Parameter)
   * Was man aber eigentlich will ist, dass in $ids immer ein array ist. Also auch ein leerer array, wenn im Formular
   * $_POST['data']['ids'] = NULL;
   * z.b. übergeben wird.
   * Dafür braucht man dann den dritten Parameter, der erst dann zurückgegeben wird, wenn $_POST['data']['ids'] === NULL ist
   *
   * Der Standardfall ist also
   * $ids = $formDataPOST->get(array('data','ids'), array(), array());
   * zu benutzen.
   * Deshalb ist im FORMDataInput der dritte Parameter unwirksam denn hier gilt $do = $default
   * zu beachten ist auch, dass der Defaultwert auch genommen wird wenn der Wert trim() === '' ist (siehe clean)
   * 
   * @param string|array $keys
   * @param $do entweder die constanten self::RETURN_NULL oder self::THROW_EXCEPTION, sind dies es nicht wird $do als defaultwert behandelt, wenn der Wert von $keys nicht gesetzt ist
   * @param $default der Wert der zurückgebeben wird, wenn die Keys zwar gefunden wurden, aber der wert der Keys === NULL ist
   * 
   */
  public function get($keys, $do = self::RETURN_NULL, $default = NULL) {
    if (is_string($keys)) $keys = explode('.',$keys);
    
    try {
      $data = $this->getDataWithKeys($keys, self::THROW_EXCEPTION);
      
    } catch (DataInputException $e) {
      /* Do */
      if ($do === self::THROW_EXCEPTION)
        throw $e;
      
      if ($do === self::RETURN_NULL)
        return NULL;
      
      return $do;
    }
    
    /* Default */
    if ($data === NULL) {
      if ($default == self::THROW_EXCEPTION) {
        $e = new EmptyDataInputException('Daten zu den Schlüssel(n): "'.implode('.',$keys).'" waren NULL');
        $e->keys = $keys;
        throw $e;
      }
      
      return $default;
    }
      
    return $data;
  }

  /**
   * Setzt einen Wert in den Daten
   * 
   * @param string|array $keys
   * @param mixed $value
   */
  public function set($keys, $value) {
    if (is_string($keys)) $keys = explode('.',$keys);
    if (is_integer($keys)) $keys = array($keys);
    
    return $this->setDataWithKeys($keys, $value);
  }
  
  
  /**
   * Entfernt Schlüssel aus den Daten
   *
   * alle Schlüssel darunter werden ebenfalls entfernt. Wird der Schlüssel nicht gefunden, passiert nichts
   */
  public function remove(array $keys) {
    $data =& $this->data;
    
    if ($keys === array() || $keys == NULL) {
      return $this;
    }
    
    $lastKey = array_pop($keys);
    foreach ($keys as $key) {
      if (!array_key_exists($key,$data)) {
        return $this;
      }
      $data =& $data[$key];
    }
    if (array_key_exists($lastKey, $data)) 
      unset($data[$lastKey]);
    
    return $this;
  }
  
  /**
   *
   * gibt auch true zurück wenn der wert der Schlüssel NULL ist
   * @return bool
   */
  public function contains($keys) {
    try {
      $this->get($keys, $do = self::THROW_EXCEPTION, self::THROW_EXCEPTION);
      
      return TRUE;
    } catch (EmptyDataInputException $e) {
      return TRUE;
    } catch (DataInputException $e) {
    }
    
    return FALSE;
  }
  
  /**
   * 
   */
  public function merge(DataInput $dataInput, Array $fromKeys = array(), $toKeys = array()) {
    $ownData = $this->get($toKeys, array(), array());
    $foreignData = $dataInput->get($fromKeys, array(), array());
    
    $this->setDataWithKeys($toKeys, array_replace_recursive($ownData,$foreignData));
    
    return $this;
  }
  
  public function isEmpty() {
    return count($this->getData()) == 0;
  }
  
  /**
   * Gibt die Daten als array zurück
   *
   * gibt auch einen array zurück wenn $this->data eigentlich kein array ist
   */
  public function toArray() {
    return (array) $this->data;
  }
}
?>