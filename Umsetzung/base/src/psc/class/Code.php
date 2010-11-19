<?php

class Code {

  /**
   * Überprüft ob die Variable eine Id ist (größer als 0 ein positiver Integer)
   * 
   * @param mixed $var
   * @return int > 0 
   */
  public static function forceId($var) {
    $int = self::forceInt($var);
    if ($int <= 0)
      throw new ForceException('Variable ist keine Id.'.self::varInfo($var));
    return $int;
  }

  /**
   * 
   * @return int
   */
  public static function forceInt($int) {
    return (int) $int;
  }

  /**
   * @param mixed $bool
   * @return bool
   */
  public static function forceBool($bool) {
    return ($bool == TRUE);
  }

  public static function forceString($var) {
    return (string) $var;
  }

  /**
   * Wandelt in einen String mit DefaultWert um
   *
   * @param mixed $var
   * @param string $default wird statt $var zurückgeben, wenn $var ein leerer String ist
   */
  public static function forceDefString($var, $default) {
    $var = self::forceString($var);
    if ($var === '')
      return $default;
    else
      return $var;
  }

  /**
   * 
   * @param mixed $value
   * @param mixed $value1,...
   */
  public static function value($value) {
    $values = func_get_args();
    array_shift($values); // value entfernen

    if (!in_array($value,$values)) {
      throw new ValueException('Wert: "'.$value.'" unbekannt. Erlaubt sind: ('.implode('|',$values).')');
    }

    return $value;
  }

  /**
   * Der erste Wert der values wird als Defaultwert genommen
   * 
   * @param mixed $value
   * @param mixed $value1,...
   */
  public static function dvalue($value, $defaultValue = NULL) {
    $values = func_get_args();
    array_shift($values); // value entfernen

    if (!in_array($value,$values)) {
      $value = array_shift($values);
    }

    return $value;
  }

  /**
   * Gibt Verbose-Informationen über einen Variablen-Typ aus
   * 
   * Diese Funktion wirklich nur zu Debug-Zwecken benutzen (in Exceptions), da sie sehr langsam ist
   * 
   * Gbit den Typ und weitere Informationen zum Typ zurück
   * @param mixed $var
   * @return string
   */
  public static function typeInfo($var) {
    $string = gettype($var);
    
    if ($string == 'object') {
      $string .= ' ('.self::getClass($var).')';
    }

    if ($string == 'array') {
      $string .= ' ('.count($var).')';
    }

    if ($string == 'resource') {
      $string .= ' ('.get_resource_type($var).')';
    }
    
    return $string;
  }

  /**
   * Gibt Verbose-Informationen über den Inhalt und den Typ einer Variablen aus
   * 
   * Diese Funktion wirklich nur zu Debug-Zwecken benutzen (in Exceptions), da sie sehr langsam ist
   * @param mixed $var
   * @return string
   */
  public static function varInfo($var) {

    return sprintf('%s %s',self::forceString($var),self::typeInfo($var));
  }
  /**
   * 
   * return-Values sind:
   * 
   * - unknown type
   * - bool
   * - int
   * - float (auch für double)
   * - string
   * - array
   * - resource:$resourcetype
   * - object:$class
   */
  public static function getType($var) {
    $type = gettype($var);

    if ($type == 'object') return 'object:'.self::getClass($var);
    if ($type == 'boolean') return 'bool';
    if ($type == 'double') return 'float';
    if ($type == 'integer') return 'int';
    if ($type == 'resource') return 'resource:'.get_resource_type($var);
    
    return $type;
  }

  /**
   * Gibt die Klasse des Objektes zurück
   * 
   * @throws Exception wenn $object kein Objekt ist
   * @param mixed $object
   * @return string
   */
  public static function getClass($object) {
    if (!is_object($object))
      throw new Exception('Cannot get Class from non-object value. '.self::varInfo($object));

    return get_class($object);
  }


  public static function loadFirePHP() {
    global $firephp;

    require_once SRC_PATH.'psc/vendor/FirePHPCore-0.3.1/lib/FirePHPCore/FirePHP.class.php';

    $firephp = FirePHP::getInstance(true);

    $firephp->setOptions(
      array(
        'maxObjectDepth' => 20,
        'maxArrayDepth' => 20,
      )
    );
        
    return $firephp;
  }
}

class ValueException extends Exception {}
class ForceException extends Exception {}

?>