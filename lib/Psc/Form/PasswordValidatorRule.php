<?php

namespace Psc\Form;

use Psc\Exception;

/**
 * Um Verwirrung zu vermeiden
 *
 * das ist nicht eine "ist das passwort richtig"-Rule
 * sondern eine "neues passwort einpflegen"-Rule
 *
 * die Rule für PasswordFields
 */
class PasswordValidatorRule extends \Psc\SimpleObject implements \Psc\Form\ValidatorRule {
  
  protected $default = NULL;
  
  /**
   * @var int
   */
  protected $minLength;
  
  public function __construct($minLength = 5) {
    $this->minLength = max(1,$minLength);
  }
  
  /**
   * @param array $data 'password'=>string, 'confirmation'=>string
   * @return string das password im klartext
   */
  public function validate($data) {
    if ($data === NULL) throw EmptyDataException::factory($this->default);
    if (!is_array($data)) throw EmptyDataException::factory($this->default);
    
    if (!array_key_exists('password',$data)) {
      throw new Exception('Schlüssel "password" existiert in den Daten nicht');
    }
    if (!array_key_exists('confirmation',$data)) {
      throw new Exception('Schlüssel "confirmation" existiert in den Daten nicht');
    }
    $password = $data['password'];
    $confirmation = $data['confirmation'];
    
    if ($password == '' && $confirmation == '') { // equals ist gewollt
      throw EmptyDataException::factory($this->default);
    }

    /* aus usability gründen erst minlength. warum?
       weil man sich vertippen kann und dann nochmal korrigiert. dann richtig hat, aber dann zu kurz hat :)
    */
    if (mb_strlen($password) < $this->minLength) {
      throw new PasswordMinLengthException($this->minLength);
    }
    
    if ($password !== $confirmation) {
      throw new PasswordConfirmationException();
    }
    
    return $data['password'];
  }
}
?>