<?php

namespace Psc\PHPJS;

use \Psc\String AS S;

class CompilerHelper {

  /**
   *
   * $js ist ein array mit folgenden Schlüsseln:
   *  - class    der Name der Klasse
   *  - comment  bereits mit /* umschlossen
   * 
   * js kann 'methods' und 'properties' als arrays enthalten
   * Diese haben jeweils folgende Schlüssel:
   *  'name'      Name der Methode / des Property
   *  'comment'   ein DocComment bereits mit /* umschlossen
   *  'body'      der JSCode der methode ohne die geschweiften Klammern außenrum.
   *              Zeilenende ist \n. Sollten NICHT eingerückt sein (außer natürlich halt vor den * die natürlich + 2 whitespaces sein solln)
   *  'params'    ein array von Parameter Namen (keine Default-Werte möglich)
   *  'default'   der Defaultwert des Properties. Dies ist ein fertiger JS Wert
   * @param array $js
   * @return jscode
   */
  public static function compileJSTree(Array $js) {
    if (!isset($js['class']))
      throw new \Psc\Exception('in js muss class gesetzt sein (string)');
      
    $br = "\n";
    $indent = 2;
    $code = (array_key_exists('comment',$js) && !empty($js['comment'])) ? $js['comment'].$br : NULL;
    $code = 'function '.$js['class'].'() {'.$br;
    if (TRUE || !isset($js['properties'])) {
      $code .= '  this.phpdata = {};'.$br;
    }
    $code .= $br;
    
    if (isset($js['properties'])) {
      foreach ($js['properties'] as $property) {
        $pCode = (array_key_exists('comment',$property) && !empty($property['comment'])) ? $property['comment'].$br : NULL;
        $pCode .= $property['name'];
        if (array_key_exists('default',$property)) {
          $pCode .= ' = '.$property['default'];
        }
        $pCode .= ';'.$br;
        
        $code .= S::indent($pCode,$indent,$br);
      }
      $code .= $br;
    }
    
    if (isset($js['methods'])) {
      foreach ($js['methods'] as $method) {
        $mCode = (array_key_exists('comment',$method) && !empty($method['comment'])) ? $method['comment'].$br : NULL;
        $mCode .= 'this.'.$method['name'].' = function('.implode(',',$method['params']).') {'.$br;
        $mCode .= S::indent($method['body'],$indent,$br);
        $mCode .= '}'.$br;
        
        $code .= S::indent($mCode,$indent,$br).$br;
      }
      $code = mb_substr($code,0,-1); // letzte br
    }
    
    $code .= '}'.$br; // ende class
   
    return $code; 
  }
  
}