<?php

namespace Psc\Code;

use Closure;
use Webforge\Common\System\File;
use Webforge\Common\System\Dir;
use Webforge\Common\Preg;

class Code extends \Webforge\Common\Util {
  
  /**
   * Wandelt in einen String mit DefaultWert um
   *
   * @param mixed $var
   * @param string $default wird statt $var zurückgeben, wenn $var ein leerer String ist
   */
  public static function forceDefString($var, $default) {
    $var = (string) $var;
    if ($var === '')
      return $default;
    else
      return $var;
  }

  /**
   * Überprüft ob $value ein Wert aus den angegebenen erlaubten Werten ist
   *
   * ist dies nicht der Fall wird eine Psc\Code\WrongValueException geworfen
   * ist dies der Fall wird $value zurückgegeben
   * @param mixed $value
   * @param mixed $value1,...
   */
  public static function value($value) {
    $values = func_get_args();
    array_shift($values); // value entfernen

    if (!in_array($value,$values)) {
      throw new \Psc\Code\WrongValueException('Wert: "'.$value.'" ist unbekannt / nicht erlaubt. Erlaubt sind: ('.implode('|',$values).')');
    }

    return $value;
  }

  /**
   * Überprüft ob $value ein Wert aus den angegebenen erlaubten Werten ist, gibt einen Default zurück
   *
   * ist $value nicht mit einem der angegeben Values identisch, wird keine Psc\Code\WrongValueException geworfen, sondern die erste angegeben Value auf $value gesetzt
   *
   * diese Funktion modifiziert für den defaultFall also $value by reference
   * @param mixed $value
   * @param mixed $defaultValue
   * @param mixed $value2,...
   */
  public static function dvalue(&$value, $defaultValue = NULL) {
    $values = func_get_args();
    array_shift($values); // value entfernen

    if (!in_array($value,$values)) {
      $value = array_shift($values);
    }

    return $value;
  }

  public static function castArray($collection, $dimSpec = NULL) {
    if ($collection instanceof \Doctrine\Common\Collections\Collection) {
      $collection = $collection->toArray();
    }
    
    if ($dimSpec === NULL) return (array) $collection; // fastcheck
    
    if (is_string($dimSpec)) {
      $dimSpec = array('dim1'=>$dimSpec);
    } else {
      $dimSpec = (array) $dimSpec;
    }
    
    if (isset($dimSpec['dim1'])) {
      foreach ($collection as $item) {
        if (Code::getType($item) != $dimSpec['dim1']) {
          throw new \Psc\Exception('Im Array ist nur Type: '.$dimSpec['dim1'].' erlaubt. Failed für Array: '.self::varInfo($collection));
        }
      }
    }
    
    return (array) $collection;
  }
  
  /**
   * @return Psc\Data\ArrayCollection
   */
  public static function castCollection($collection) {
    if ($collection instanceof \Psc\Data\ArrayCollection) {
      return $collection;
    } elseif (is_array($collection)) {
      return new \Psc\Data\ArrayCollection($collection);
    } elseif ($collection instanceof \Doctrine\Common\Collections\Collection) {
      return new \Psc\Data\ArrayCollection($collection->toArray());
    } else {
      return new \Psc\Data\ArrayCollection((array) $collection);
    }
  }
  
  /**
   * Gibt zurück ob $collection mit foreach durchlaufen werden kann
   *
   * @return bool
   */
  public static function isTraversable($collection) {
    return is_array($collection) || $collection instanceof \stdClass || $collection instanceof \Traversable;
  }
  
  /**
   * @return Closure
   */
  public static function castGetter($getter) {
    if (!($getter instanceof Closure)) {
      $get = mb_strpos($getter,'get') !== 0 ? 'get'.ucfirst($getter) : $getter;
      $getter = function($o) use($get) {
        return $o->$get();
      };
    }
    
    return $getter;
  }

  /**
   * @return Closure
   */
  public static function castSetter($setter) {
    if (!($setter instanceof Closure)) {
      $set = mb_strpos($setter,'set') !== 0 ? 'set'.ucfirst($setter) : $setter;
      $setter = function($o,$value) use($set) {
        return $o->$set($value);
      };
    }
    
    return $setter;
  }
  
  /**
   * Gibt die id als int zurück wenn $id > 0 ist ansonsten $default
   *
   * sieht blöd aus, macht aber total viel Schreibarbeit wett
   * ist äquivalent zu:
   * self::cast($id, function ($id) { return $id > 0; }, 'integer', $default);
   */
  public static function castId($id, $default = NULL) {
    if (is_integer($id) && $id > 0) return $id;
    if (ctype_digit($id) && ($id = (int) $id) && $id > 0) {
      return $id;
    } else {
      return $default;
    }
  }
  
  /**
   * Gibt die $value als $type zurück wenn die $condition($value) TRUE ergibt, ansonsten $default
   * @param string $type object|array|bool|integer|null|string|float
   */
  public static function cast($value, Closure $condition, $type, $default = NULL) {
    return $condition($value) == TRUE ? settype($value, $type) : $default;
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
      throw new \Psc\Exception('Cannot get Class from non-object value. '.self::varInfo($object));
    
    return get_class($object);
  }
  
  public static function dumpEnv() {
    print '$_SERVER = ';var_export($_SERVER); print ';'."\n";
    print '$_GET = '; var_export($_GET); print ';'."\n";
    print '$_POST = '; var_export($_POST); print ';'."\n";
  }
  
  /**
   * Gibt den Namespace der Klasse zurück
   *
   * gibt NULL zurück für Klassen die im Root-Namespace sind
   * deshalb ist es hiermit schwierig zu unterscheiden, ob ein nicht-FQN einen Namespace hat
   *
   * getNamespace('\stdClass') === NULL // true
   * getNamespace('stdClass') === NULL // auch true
   * 
   * es kommt also jeweils auf den Kontext an in dem getNamespace auf einem String aufgerufen wird (vergleiche use in php)
   * dieser ist immer MIT \ davor
   *
   * p.S: @TODO schöner wäre hier als immer immer immer mit \ zurückzugeben, dann wäre nämlich obiges Beispiel auch unterscheidbar
   */
  public static function getNamespace($className) {
    $ns = NULL;
    if (($p = mb_strrpos($className,'\\')) !== FALSE) {
      $ns = mb_substr($className,0,$p);
    }
    if ($ns == '')
      return NULL;
    
    if (mb_strpos($className,'\\') !== 0) {
      $ns = '\\'.$ns;
    }
    
    return $ns;
  }
  
  /**
   * Erweitert einen nicht qualifzierten Klassenamen mit einem Namespace
   *
   * nicht qualifizierte Klassenamen sind diese ohne \ am Anfang und ohne self::getNamespace()
   * wird root als leer übergeben, ist nicht gewährleistet, dass für alle parameter ein \ vor der Klasse steht:
   * z. B. (blubb\className) wird nicht \blubb\className  da blubb der namespace der klasse ist und somit nicht expanded wird
   * @param $expandNamespace ohne \ am Ende (für "root" also NULL übergeben)
   */
  public static function expandNamespace($classOrClassName, $expandNamespace) {
    if (mb_strpos($classOrClassName, '\\') !== 0 && self::getNamespace($classOrClassName) === NULL) {
      return $expandNamespace.'\\'.ltrim($classOrClassName,'\\');
    }
    
    return $classOrClassName;
  }
  
  /**
   * Gibt nur den Namen (ohne Namespace) ohne \ davor zurück
   * @param string|object $className
   */
  public static function getClassName($className) {
    if (is_object($className)) $className = self::getClass($className);
    
    $c = $className;
    if (mb_strpos($className,'\\') !== FALSE) {
      $p = explode('\\',$className);
      $c = array_pop($p);
    }
    return $c;
  }
  
  
  /**
   * Gibt das Verzeichnis eines Namespaces in einem Verzeichnis zurück
   *
   * dies nimmt einfach den Namespace
   * @return Dir absolute im angebenen $classPath oder relatives Verzeichnis
   */
  public static function namespaceToPath($namespace, Dir $classPath = NULL) {
    // end und anfangs backslashs weg
    $namespace = trim($namespace,'\\'); 
    
    if (!isset($classPath)) {
      $classPath = new Dir('.'.DIRECTORY_SEPARATOR);
    }
    
    return $classPath->sub(str_replace('\\','/',$namespace).'/');
  }
  
  /**
   * Gibt den Dateinamen zu einer Klasse zurück
   *
   * Konvention: der relative Pfad zur Datei der Namespace wird mit Verzeichnissen abgebildet der Klassenname als php-Datei
   * @return File wenn $root gesetzt ist dies der absolute pfad, sonst relativ
   */
  public static function mapClassToFile($classFQN, Dir $root = NULL) {
    $ns = self::getNamespace($classFQN);
    $c = self::getClassName($classFQN);
   
    if (!isset($root)) {
      $root = new Dir('.'.DIRECTORY_SEPARATOR);
    }
      
    return new File(self::namespaceToPath($ns, $root), $c.'.php');
  }
  
  /**
   * Gibt den Namen der Klasse für eine Datei zurück
   *
   * beruht auf der Konvention, dass jedes Verzeichnis ein Namespace ist und der Dateiname der Klassename ist
   *
   * Beispiel siehe Test
   *
   * 
   * @param Dir $root darf nicht das verzeichnis des Namespaces sein. also nicht base\src\SerienLoader sondern base\src\
   * @param string $style wird _ übergeben wird z. B. der PHPWord Style benutzt (PSR-0 mit Underscore)
   * @return string class mit \ davor
   */
  public static function mapFileToClass(File $classFile, Dir $root = NULL, $style = '\\') {
    $classDir = clone $classFile->getDirectory();
    if (isset($root))
      $classDir->makeRelativeTo($root);
    
    $pa = $classDir->getPathArray();
    
    if ($style === '\\') {
      $ns = (count($pa) > 1) ? '\\'.implode('\\',array_slice($pa,1)) : NULL;
    
      return $ns.'\\'.$classFile->getName(File::WITHOUT_EXTENSION);
    } elseif ($style === '_') {
      $parts = (count($pa) > 1) ? array_slice($pa,1) : array();
      $parts[] = $classFile->getName(File::WITHOUT_EXTENSION);
      
      return implode('_',$parts);
    } else {
      \Psc\Exception('Der Style: '.self::varInfo($style).' ist nicht bekannt');
    }
  }
  
  
  /**
   * @param string $dashString something like nice-class
   * @return string in CamelCase something like NiceClass
   */
  public static function dashToCamelCase($dashString) {
    return ucfirst(Preg::replace_callback($dashString, '/\-([a-zA-Z])/', function ($match) {
      return mb_strtoupper($match[1]);
    }));
  }
  
  public static function camelCaseToDash($camelCaseString) {
    return \Psc\HTML\HTML::string2class($camelCaseString);
  }

  public static function getMemoryUsage() {
    $limit = ini_get('memory_limit');
    
    if ((int) $limit > 0) {
      $limit = (int) $limit / (1024*1024);
    } 

    if ($limit === -1) {
      return sprintf('%02.2f MB (unlimited memory)', memory_get_usage()/(1024*1024));
    } else {
      return sprintf('%02.2f MB von %02.2f MB', memory_get_usage()/(1024*1024), $limit);
    }
  }
}
?>