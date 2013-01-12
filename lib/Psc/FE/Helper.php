<?php

namespace Psc\FE;

use Psc\A;
use Psc\Code\Code;
use Closure;

class Helper extends \Psc\Object {
  
  /**
   * @var mixed $print Closure|String wenn ein String wird dies als Attribute gesehen welches mit get$print() vom Objekt geladen werden kann
   */
  public static function listObjects($objectCollection, $sep = ', ', $print = 'Id', $andsep = NULL, Closure $formatter = NULL) {
    $objectCollection = Code::castArray($objectCollection);
    
    $cnt = count($objectCollection);
    if (isset($andsep) && $cnt > 1) {
      $last = array_pop($objectCollection);
    } else {
      $andsep = NULL;
    }
    
    if (is_string ($print) || $print instanceof Closure) {
      $g = Code::castGetter($print);
    } else {
      throw new \InvalidArgumentException('Unbekannter Parameter für $print: '.Code::varInfo($print));
    }
    
    $formatter = $formatter ?: function ($item) { return (string) $item; };
    $ret = A::implode($objectCollection, $sep, function ($object, $key) use ($g, $formatter) {
      // formatiere das $print-Attribute von $object
      return $formatter($g($object, $key), $key);
    });
    
    if(isset($andsep) && $last != NULL) {
      $ret .= $andsep.$formatter($g($last));
    }
    
    return $ret;
  }
  
    
  /**
   * @var mixed $print Closure|String wenn ein String wird dies als Attribute gesehen welches mit get$print() vom Objekt geladen werden kann
   */
  public static function listStrings($collection, $sep = ', ', $andsep = NULL, Closure $formatter = NULL) {
    $collection = Code::castArray($collection);
    
    $cnt = count($collection);
    if (isset($andsep) && $cnt >= 2) {
      $last = array_pop($collection);
    } else {
      $andsep = NULL;
    }
    
    $formatter = $formatter ?: function ($item) { return (string) $item; };
    $ret = A::implode($collection, $sep, $formatter);
    
    if(isset($andsep) && $last != NULL) {
      $ret .= $andsep.$formatter($last);
    }
    
    return $ret;
  }
}