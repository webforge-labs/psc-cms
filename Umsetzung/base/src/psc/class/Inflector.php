<?php

class Inflector extends Object {

  protected static $cache = array('singular'=>array(), 'plural'=> array());

  protected static $uncountable = array(); // siehe inc.config.php

  protected static $irregular = array(); // siehe inc.config.php

  protected static $init = FALSE;

  protected function init() {
    if (!self::$init) {
      try {
        self::$uncountable = array_merge(self::$uncountable,Config::get('inflector','uncountable'));
        self::$irregular = array_merge(self::$irregular,Config::get('inflector','irregular'));
      } catch (Exception $e) {
        Debug::notice('Inflector ohne config geladen!');
      }
      self::$init = TRUE;
    }
  }

  public function singular($plural) {
    self::init();

    /* cache hit */
    if (array_key_exists($plural,self::$cache['singular']))
      return self::$cache['singular'][$plural];

      /* cache miss */
    if (in_array($plural,self::$uncountable)) {
      $singular = $plural;
    
    } elseif (($irregularRev = array_flip(self::$irregular)) && array_key_exists($plural, $irregularRev)) {
      $singular = $irregularRev[$plural];
    
    } elseif (Preg::match($plural, '/[sxz]es$/') > 0 || Preg::match($plural,'/[^aeioudgkprt]hes$/') > 0) {
			// entferne "es"
			$singular = mb_substr($plural, 0, -2);
		
    } elseif (Preg::match($plural,'/[^aeiou]ies$/')) {
			$singular = mb_substr($plural, 0, -3).'y';
		
    } elseif (mb_substr($plural, -1) === 's' && mb_substr($plural, -2) !== 'ss') {
			$singular = substr($plural, 0, -1);
		} else {
      throw new Exception('Kann keinen Singular von '.$plural.' erstellen. in der Config können Singular/Plural Formen hinzugefügt werden');
    }

    self::$cache['singular'][$plural] = $singular;
    return $singular;
  }

  public function plural($singular) {
    self::init();

    /* cache hit */
    if (array_key_exists($singular,self::$cache['singular']))
      return self::$cache['plural'][$singular];

    /* cache miss */
    if (in_array($singular,self::$uncountable)) {
      $plural = $singular;
    
    } elseif (array_key_exists($singular, self::$irregular)) {
      $plural = self::$irregular[$singular];

    } elseif (Preg::match($singular, '/[sxz]$/') || Preg::match($singular, '/[^aeioudgkprt]h$/')) {
			$plural = $singular.'es';
		
    } elseif (Preg::match($singular, '/[^aeiou]y$/')) {
			// ändere "y" to "ies"
			$plural = substr_replace($singular, 'ies', -1);
		} else {
			$plural = $singular.'s';
		}

    self::$cache['plural'][$singular] = $plural;
    return $plural;
  }
}

?>