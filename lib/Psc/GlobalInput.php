<?php

namespace Psc;

use Psc\Form\DataInput AS FormDataInput;

class GlobalInput {
  
  protected static $instances;
  
  /**
   * @return \Psc\Form\DataInput
   */
  public static function instance($name) {
    if (!isset(self::$instances[$name])) {
      switch ($name) {
        case 'GET':
          $input = $_GET;
          break;
        case 'POST':
          $input = $_POST;
          break;
        case 'COOKIE':
          $input = $_COOKIE;
          break;
        default:
          throw new \Psc\Exception('Parameter: '.Code::varinfo($name).' nicht erlaubt');
      }
      self::$instances[$name] = new FormDataInput($input, FormDataInput::TYPE_ARRAY);
    }
    
    return self::$instances[$name];
  }
  
  public static function GET() {
    $keys = func_get_args();
    return self::setOrGetInput('GET',$keys);
  }

  public static function POST() {
    $keys = func_get_args();
    return self::setOrGetInput('POST',$keys);
  }

  public static function COOKIE() {
    $keys = func_get_args();
    return self::setOrGetInput('COOKIE',$keys);
  }
  
  /**
   *
   * @param string $name der Name der Instance (GET|POST|COOKIE)
   * @param array $keys wenn der erste key GPC::SET ist wird der letzte Key als Value interpretiert und im input von $name gesetzt
   */
  protected static function setOrGetInput($name, Array $keys) {
    if (count($keys) == 0) {
      return self::instance($name);
    }
    
    if (isset($keys[0]) && $keys[0] == GPC::SET) {
      array_shift($keys);
      $value = array_pop($keys);
      return self::instance($name)->setDataWithKeys($keys, $value);
    } else {
      return self::instance($name)->getDataWithKeys($keys);
    }
  }
}
?>