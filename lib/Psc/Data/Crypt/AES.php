<?php

namespace Psc\Data\Crypt;

class AES extends \Psc\Object {
  
  protected $password;
  
  public function __construct($password) {
    $this->password = $password;
  }
  
  /**
   * Gibt das encryptete in base64 zurück
   */
  public function encrypt($clearText) {
    return base64_encode(
                         mcrypt_encrypt(MCRYPT_RIJNDAEL_256,
                                        $this->password,
                                        $clearText,
                                        MCRYPT_MODE_ECB,
                                        mcrypt_create_iv(
                                                         mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB),
                                                         MCRYPT_RAND
                                                        )
                                       )
                        );
  }
  
  /**
   * Gibt das decryptete in Klartext zurück
   *
   * @param string $encrypted in base64
   */
  public function decrypt($encrypted) {
    return trim(
                mcrypt_decrypt(
                               MCRYPT_RIJNDAEL_256,
                               $this->password,
                               base64_decode($encrypted),
                               MCRYPT_MODE_ECB,
                               mcrypt_create_iv(
                                                mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB),
                                                MCRYPT_RAND
                                                )
                               )
                );
  }
  
}
?>