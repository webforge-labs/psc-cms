<?php

namespace Psc\Data;

use Psc\Data\Type\StringType;
use Psc\Data\Type\IntegerType;
use Psc\Data\Type\BooleanType;
use Psc\Data\Type\ArrayType;
use Psc\Data\Type\Type;
use Webforge\Common\ArrayUtil AS A;

class RandomGenerator extends \Psc\SimpleObject {
  
  protected $chars = array();
  
  public function __construct() {
    for ($x = 33; $x <= 126; $x++) {
      $this->chars[] = chr($x);
    }
    $this->chars[] = 'ä';
    $this->chars[] = 'ü';
    $this->chars[] = 'ö';
    $this->chars[] = 'Ä';
    $this->chars[] = 'Ü';
    $this->chars[] = 'Ö';
    $this->chars[] = 'ß';
  }
  
    public function generateData(Type $type) {
    switch ($type->getName()) {
      case 'String':
        return $this->generateString(15);
      
      case 'Integer':
        return mt_rand(0,99999)*(mt_rand(0,1) === 0 ? -1 : 1); // negativ, positiv, 0-xxxx
      
      case 'Array':
        // wir füllen den array mit random strings, oder whatever, numerische schlüssel
        $array = array();
        if ($type->isTyped()) {
          for ($i=1; $i<=15; $i++) {
            $array[] = $this->generateData($type->getType());
          }
        } else {
          $randomTypes = array(new StringType, new IntegerType, new BooleanType);
          for ($i=1; $i<=15; $i++) {
            $array[] = $this->generateDataRandomType($randomTypes);
          }
        }
        return $array;
      
      case 'Boolean':
        return mt_rand(0,1) === 1;
    }
    
    throw new \Psc\Code\NotImplementedException('Kein switch für randomData für: '.$type->getName());
  }
  
  public function generateDataRandomType(Array $types) {
    return $this->generateData(A::randomValue($types));
  }

  public function generateString($length) {
    $length = max(1,$length);
    $string = NULL;
    $l = count($this->chars)-1;
    for ($i = 1; $i<=$length; $i++) {
      $string .= $this->chars[mt_rand(0,$l)];
    }

    return $string;    
  }
}
?>