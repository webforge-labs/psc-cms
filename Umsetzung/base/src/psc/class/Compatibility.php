<?php

/**
 * Diese Klasse kümmert sich um kleine Helfer Funktionen die Kompatibel gemacht werden sollen
 *
 * Jenachdem wo eine Klasse verwendet wird, wird diese hier angepasst
 */
class Compatibility {
  
  const KOHANA = 'kohana';
  const WORDPRESS = 'wordpress';
  
  public $mode = NULL;
  
  public $from = 'psc';
  
  public function __construct() {
    $this->setMode();

    $this->load();
  }
  
  
  /**
   * Lädt im Module-Compat Verzeichnis die $from2$mode.php in der alles stehen darf
   */
  public function load() {
    $dir = SRC_PATH.'psc'.DIRECTORY_SEPARATOR.'compat'.DIRECTORY_SEPARATOR;
    
    $fname = $dir.$this->from.'2'.$this->mode.'.php';
    if (is_readable($fname)) {
      require_once $fname;
    }
  }
  
  public function setMode() {
    if (defined('KOHANA')) {
      $this->mode = self::KOHANA;
    }
    
    if (defined('ABSPATH') && class_exists('WP')) {
      $this->mode = self::WORDPRESS;
    }
  }
}

?>
