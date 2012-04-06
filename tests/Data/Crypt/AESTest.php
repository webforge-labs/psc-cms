<?php

namespace Psc\Data\Crypt;

use \Psc\Data\Crypt\AES;

class AESTest extends \Psc\Code\Test\Base {

  public function testIntegrity() {      
    $password = 'meinpasswort';
    $value = 'The Quick Brown Fox jumps over the lamb';
    
    $aes = new AES($password);
    $encrypted = $aes->encrypt($value);
    
    $this->assertEquals($value, $aes->decrypt($encrypted));
  }
}

?>