<?php

namespace Psc\Form;

use Psc\Doctrine\Helper as DoctrineHelper;
use Psc\Code\Code;
use Psc\Code\Callback;

class TagsValidatorRule implements ValidatorRule {
  
  protected $tagsClass;
  
  /**
   * @param string $tagsClass muss die statische Methode toCollection haben, die einen String mit einem Trennzeichen und Tags erwartet
   */
  public function __construct($tagsClass, $em = NULL) {
    $this->tagsClass = $tagsClass;
  }
  
  public function validate($data) {
    $e = new EmptyDataException();
    $e->setDefaultValue(array());
    
    if ($data === NULL) throw $e;
    if (trim($data) == ',') throw $e;
	
    $cb = new Callback($this->tagsClass,'toCollection');
    return $cb->call(array($data));
  }
}

?>