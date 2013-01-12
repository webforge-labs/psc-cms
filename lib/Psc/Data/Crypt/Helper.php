<?php

namespace Psc\Data\Crypt;

class Helper {
  
  public static function hex2bin($hexstring) {
    return pack("H*" , $hexstring);
  }
  
}

?>