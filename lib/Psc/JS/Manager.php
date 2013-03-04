<?php

namespace Psc\JS;

use 
    \Psc\Config,
    \Webforge\Common\ArrayUtil AS A,
    \Webforge\Common\System\File,
    \Psc\System\System,
    \Psc\JS\Helper AS JSHelper,
    \Psc\PSC,
    \Webforge\Common\String as S
;

class Manager extends \Psc\DependencyManager {
  
  protected static $instances;
  
  /**
   * Ist z.B: für den DefaultManager (hardcoded) /JS/
   *
   * dieser URL-Part wird vor jede js Datei bei der Ausgabe gestellt, sofern es nicht überschrieben wird
   */
  protected $url;
  
  /**
   * Erstellt eine Datei die alle Javascript dateien beeinhaltet in htdocs/jsc (ist ein langer hash)
   *
   * wenn dies nicht gewünscht ist, dies hier auf false stellen
   */
  protected $compile = TRUE;
  
  /**
   * @param string $name der Name des JSManagers
   */
  public function __construct($name) {
    parent::__construct($name);
    
    if ($this->name == 'default') {
      $this->url = Config::getDefault('js.url','/js/');
      $this->compile = !PSC::inProduction();
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
    $code = NULL;
    $hash = NULL;
    
    /* immer kompilieren, aber nur im dev modus einzeln ausgeben (sieh oben) */
    $jscDir = PSC::get(PSC::PATH_HTDOCS)->append('/jsc/');
    
    $hash = PSC::getProject()->getLowerName();
    
    $file = new File($jscDir, $hash.'.min.js');
    if (PSC::inProduction()) {
      $jsDir = PSC::get(PSC::PATH_HTDOCS)->append($this->url.'/');
      
      $jsFiles = array();
      $mtime = 0;
      foreach ($this->enqueued as $alias) {
        $jsFiles[] = $f = new File($jsDir,$this->files[$alias]['name']);
        $mtime = max($mtime,filemtime((string) $f));
      }
      if (!$file->exists() || $mtime > filemtime($file)) { // hier fehlt die bedingung, dass wenn man enqueued oder dequeud auch neu kompiliert werden muss
        $header = NULL;
        foreach ($jsFiles as $jsFile) {
          $info = clone $jsFile;
          $info->makeRelativeTo(PSC::get(PSC::PATH_HTDOCS)->append('/js/'));
          $header .= ltrim($info,'./\\')."\n";
          if (!S::endsWith((string) $jsFile,'min.js')) {
            $target = clone $jsFile;
            $target->setExtension('min.js');
            
            /* Try-Minify */
            $jsFile = JS::minify($jsFile);
          }
          $code .= $jsFile->getContents()."\n\n\n";
        }
        $file->writeContents("/* Compiled Files:\n".$header."\n*/\n".$code);
      }
    }
      
    if ($this->compile) {
      $html = array(JSHelper::load('/jsc/'.$file->getName(File::WITH_EXTENSION))->setOption('br.closeTag',FALSE));
    } else {
      $html = array();

      foreach ($this->enqueued as $alias) {
        $html[] = JSHelper::load($this->url.$this->files[$alias]['name'])->setOption('br.closeTag',FALSE);
      }
    }
    
    return $html;
  }
}
?>