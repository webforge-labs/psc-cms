<?php

namespace Psc\Net\HTTP;

use Psc\Preg;
use Psc\DateTime\DateTime;
use Psc\Code\Code;

/**
 * This is crap, better use symfony for this
 *
 * @TODO header fields are case sensitiv - which is wrong!
 */
class Header extends \Psc\Object {
  
  const PARSE = 'parse';
  const RETURN_VALUE = 'return_value';
  
  protected $values = array();
  
  protected $version = '1.1';
  
  public function send() {
    $this->sendStatusLine();
    
    foreach ($this->values as $key => $value) {
      if (is_array($value)) {
        foreach ($value as $v) {
          $this->sendPHPHeader($key,$v, FALSE);
        }
      } else {
        $this->sendPHPHeader($key,$value);
      }
    }
  }
  
  public function __toString() {
    $string = NULL;
    foreach ($this->values as $key => $value) {
      if (is_array($value)) {
        foreach ($value as $v) {
          $string .= $key.': '.$v."\n";
        }
      } else {
        $string .= $key.': '.$value."\n";
      }
    }
    return $string;
  }
  
  public function getFields() {
    return $this->values;
  }
  
  public function setField($name, $value) {
    if (!isset($this->values[$name]))
      $this->values[$name] = NULL;
      
    $this->values[$name] = $this->mergeField($name, $this->values[$name], $value);
    return $this;
  }
  
  protected function mergeField($name, $currentValue, $value) {
    if (in_array($name, array('Cache-Control','Pragma'))) {
      // diese müssen nach , getrennt gemerged werden
      $currentValue = $this->parseField($name, $currentValue);
      $value = $this->parseField($name, $value);
      $currentValue = array_unique(array_merge($currentValue, $value));
      sort($currentValue);
    } elseif (is_array($currentValue)) {
      // füge zu bestehendem Array hinzu
      if (!in_array($value, $currentValue))
        $currentValue[] = $value;
    } elseif ($currentValue === NULL) {
      $currentValue = $value;
    } else {
      // überschreibe nicht sondern speichere in unter array für value
      if ($currentValue !== $value) // neu
        $currentValue = array($currentValue, $value);
    }
    
    return $currentValue;
  }
  
  public function getField($name, $do = self::RETURN_VALUE) {
    $value = array_key_exists($name,$this->values) ? $this->values[$name] : NULL;
    
    if ($do === self::PARSE) {
      $value = $this->parseField($name, $value);
    }
    
    return $value;
  }
  
  /**
   * @return bool
   */
  public function hasField($name) {
    return array_key_exists($name,$this->values);
  }

  /**
   * @return NULL|Psc\DateTime\DateTime
   */
  public function getDate() {
    $str = $this->getField('Date');
    if ($str !== NULL) {
      //DateTime::COOKIE
    /*
     @TODO parseDate in HTTP:: Klasse 
     HTTP applications have historically allowed three different formats for the representation of date/time stamps:

      Sun, 06 Nov 1994 08:49:37 GMT  ; RFC 822, updated by RFC 1123
      Sunday, 06-Nov-94 08:49:37 GMT ; RFC 850, obsoleted by RFC 1036
      Sun Nov  6 08:49:37 1994       ; ANSI C's asctime() format
    */

      return DateTime::parse(DateTime::RFC1123 , $str);
    }
    return NULL;
  }
  
  /* Parsing Funktionen um einen Header aus einem String zu erstellen */
  
  protected function parseField($name, $value) {
    switch ($name) {
      case 'Content-Disposition':
        $this->assertNotEmpty($value, $name);
        
        // @TODO das ist ganz schön stümperhaft aber geht erstmal
        if (Preg::match($value, '/^(.*?);\s+filename="(.*)"$/', $match)) {
          $value = (object) array('type'=>$match[1],'filename'=>$match[2]);
        } else {
          throw new HeaderFieldParsingException('Content-Disposition: "'.$value.'" kann nicht geparsed werden');
        }
        break;
      
      case 'Cache-Control':
      case 'Pragma':
        $value = preg_split('/\s*,\s*/',$value);
        break;
    }
    return $value;
  }
  
  protected function assertNotEmpty($value, $name) {
    if (empty($value))
      throw new HeaderFieldNotDefinedException('Der Header: '.$name.' ist nicht gesetzt');
  }
  
  public function parseFrom($string) {
    $fields = explode("\r\n", Preg::replace($string, '/\x0D\x0A[\x09\x20]+/', ' '));
    array_shift($fields); // status line wegnehmen, denn das wird in den ableitenden geparsed
    
    /*
       message-header = field-name ":" [ field-value ]
       field-name     = token
       field-value    = *( field-content | LWS )
       field-content  = <the OCTETs making up the field-value
                        and consisting of either *TEXT or combinations
                        of token, separators, and quoted-string>
    */
    foreach($fields as $field)  {
      if (Preg::match($field, '/([^:]+): (.+)/m', $match)) {
        list($NULL, $name, $value) = $match;
        $name = Preg::replace(mb_strtolower(trim($name)), '/(?<=^|[\x09\x20\x2D])./e', 'mb_strtoupper("\0")');
        
        $this->setField($name,$value);
      }
    }
  }
  
  public static function parse($rawHeader) {
    $header = new static();
    $header->parseFrom($rawHeader);
    return $header;
  }
  
  public function sendPHPHeader($key, $value, $replace = TRUE, $code = NULL) {
    if (mb_strpos($value, "\n") || mb_strpos($value, "\r")) {
      throw new \InvalidArgumentException(sprintf("Value für '%s' darf keine Newline enthalten: '%s'",$key, Code::varInfo($value)));
    }
    
    if (isset($key))
      Header($key.': '.$value, $replace, $code);
    else
      Header($value, $replace, $code);
      
    return $this;
  }
}
?>