<?php

namespace Psc\JS;

use Webforge\Common\ArrayUtil AS A;
use Psc\JS\Helper AS js;
use Psc\PSC;
use Webforge\Common\String as S;
use Webforge\Framework\Project;

class Manager extends \Psc\DependencyManager {
  
  protected static $instances;
  
  /**
   * Ist z.B: für den DefaultManager (hardcoded) /JS/
   *
   * dieser URL-Part wird vor jede js Datei bei der Ausgabe gestellt, sofern es nicht überschrieben wird
   */
  protected $url;
  
  /**
   * @param string $name der Name des JSManagers
   */
  public function __construct($name, Project $project = NULL) {
    parent::__construct($name);
    $this->project = $project ?: PSC::getProject();
    
    if ($this->name == 'default') {
      $this->url = $this->project->getConfiguration()->get(array('js', 'url'), '/js/');
    }
  }
  
  /**
   * @return JSManager
   */
  public static function instance($name = 'default') {
    if (!isset(self::$instances[$name])) {
      Manager::$instances[$name] = new Manager($name);
    }
    return self::$instances[$name];
  }

  /**
   * Gibt das HTML zum Laden von allen JS Dateien aus
   */
  public function load($glue = "%s \n") {
    print A::join($this->getHTML(),"%s \n");
  }
  
  /**
   *
   * gibt einen Array mit HTML Tags zurück die Script-Tags sind und jeweils ein JS File laden
   * @return HTMLTag[]
   */
  public function getHTML() {
    $html = array();
    foreach ($this->enqueued as $alias) {
      $html[] = js::load($this->url.$this->files[$alias]['name'])->setOption('br.closeTag',FALSE);
    }
    
    return $html;
  }
}
