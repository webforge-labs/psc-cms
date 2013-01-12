<?php

namespace Psc\Form;

class EmailValidatorRule implements ValidatorRule {
  
  public function validate($data) {
    if ($data === NULL) throw new EmptyDataException();

/*
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
    $email = trim($data);

    if (preg_match('/^[-_a-z0-9\'+*$^&%=~!?{}]++(?:\.[-_a-z0-9\'+*$^&%=~!?{}]+)*+@(?:(?![-.])[-a-z0-9.]+(?<![-.])\.[a-z]{2,6}|\d{1,3}(?:\.\d{1,3}){3})(?::\d++)?$/iD', (string) $email) > 0) {
      return $email;
    }
    
    throw new \Psc\Exception('Konnte nicht validiert werden');
  }
}

?>