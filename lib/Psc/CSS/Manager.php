<?php

namespace Psc\CSS;

use \Psc\A;

class Manager extends \Psc\DependencyManager implements \Psc\HTML\HTMLInterface {
  
  protected static $instances;
  
  /**
   * Ist z.B: für den DefaultManager (hardcoded) /css/
   *
   * dieser URL-Part wird vor jede css Datei bei der Ausgabe gestellt, sofern es nicht überschrieben wird
   */
  protected $url = '/';
  
  /**
   * @param string $name der Name des CSSManagers
   */
  public function __construct($name) {
    parent::__construct($name);
    
    if ($this->name == 'default') {
      $this->url = '/css/';
    }
  }

  /**
   * @return CSSManager
   */
  public static function instance($name = 'default') {
    if (!isset(self::$instances[$name])) {
      $c = __CLASS__;
      self::$instances[$name] = new $c($name);
    }
    return self::$instances[$name];
  }
  
  /**
   * Gibt das HTML zum Laden von allen CSS Dateien aus
   */
  public function load($glue = "%s \n") {
    print A::join($this->getHTML(),"%s \n");
  }
  
  /**
   *
   * gibt einen Array mit HTML Tags zurück die REL-Tags sind und jeweils ein CSS File laden
   * @return HTMLTag[]
   */
  public function getHTML() {
    $html = array();
    foreach ($this->enqueued as $alias) {
      $file = $this->files[$alias]['name'];
      $html[] = Helper::load(mb_strpos($file, '/') === 0 ? $file : $this->url.ltrim($file,'/'));
    }
    return $html;
  }
  
  /**
   * @param string $url
   * @chainable
   */
  public function setUrl($url) {
    $this->url = rtrim($url,'/').'/';
    return $this;
  }

  /**
   * @return string
   */
  public function getUrl() {
    return $this->url;
  }
  
  /**
   * @return string
   */
  public function html() {
    return implode("\n",$this->getHTML());
  }
}
?>