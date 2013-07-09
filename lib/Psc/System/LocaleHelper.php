<?php

namespace Psc\System;

class LocaleHelper {
  
  public static function toLanguage($locale, Array $avaibleLanguages = array('de','en','fr')) {
    $pre = mb_substr($locale, 0, 2);
    if (in_array($pre, $avaibleLanguages)) {
      return $pre;
    }
    
    throw new \Psc\Exception(sprintf("Cannot parse language from '%s'", $locale));
  }
}
