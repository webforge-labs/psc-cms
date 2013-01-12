<?php

namespace Psc;

class Inflector extends \Psc\Object {

  protected static $cache = array('singular'=>array(), 'plural'=> array());

  protected static $uncountable = array(
	);

  protected static $irregular = array(
    'child'=>'children',
    'Child'=>'Children',
		'news'=>'news',
		'News'=>'News',
		'NewsEntry'=>'NewsEntries',
		'newsentry'=>'newsentries'
  );

  protected static $init = FALSE;

  protected static function init() {
    if (!self::$init) {
      self::$uncountable = array_merge(self::$uncountable,Config::getDefault(array('inflector','uncountable'),array()));
      self::$irregular = array_merge(self::$irregular,Config::getDefault(array('inflector','irregular'),array()));
      self::$init = TRUE;
    }
  }

  public static function singular($plural) {
    self::init();

    /* cache hit */
    if (array_key_exists($plural,self::$cache['singular']))
      return self::$cache['singular'][$plural];

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
      return $plural; // fail silent
      throw new Exception('Kann keinen Singular von '.$plural.' erstellen. in der Config können Singular/Plural Formen hinzugefügt werden');
    }

    self::$cache['singular'][$plural] = $singular;
    return $singular;
  }

  public static function plural($singular) {
    self::init();
    if (!is_string($singular)) throw new \InvalidArgumentException('Erster parameter muss ein string sein. '.\Psc\Code\Code::varInfo($singular));

    /* cache hit */
    if (array_key_exists($singular,self::$cache['plural']))
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
  

  /**
   * Gibt den (lcfirst) LowerCase Name für einen Klassennamen zurück
   *
   * OID => oid
   * OIDMeta => oidMeta
   * SomeClassName => someClassName
   * 
   * @param $className ohne Namespace
   */
  public function propertyName($className) {
    if (!is_string($className) || mb_strpos($className, '\\') !== FALSE) {
      throw new \InvalidArgumentException(sprintf("ClassName %s kann nicht zu propertyName umgewandelt werden. Er muss ein String sein und darf nur der ClassName sein", \Psc\Code\Code::varInfo($className)));
    }
    
    // 1. Fall: Klasse ist CamelCase. wichtig ist hierbei dass das erste a klein ist
    if (Preg::match($className, '/^[A-Z]{1}[a-z]{1}/') > 0) {
      return lcfirst($className);
    }
    
    // 2. Fall: Klassename ist eine Abkürzung. Z. B. OID (aka: der ganze String sind Großbuchstaben)
    if (Preg::match($className, '/^[A-Z]+$/')) {
      return mb_strtolower($className);
    }
    
    // 3. Fall: KlassenName ist mixed sowas wie OIDMeta
    // teile auf in m[1] = 'OID' m[2] = 'Meta'
    $m = array();
    if (Preg::match($className, '/^([A-Z]+)([A-Z]{1}[a-z]{1}.*)$/', $m) > 0) {
      return mb_strtolower($m[1]).$m[2];
    }
    
    throw new \RuntimeException(sprintf("Kein matching Case für ClassName '%s'", $className));
  }
	
	public static function create() {
		return new static();
	}
}
?>