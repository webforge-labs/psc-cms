<?php

namespace Psc\TPL;

use Webforge\Common\System\Dir;
use Webforge\Common\System\File;
use Psc\Exception;
use Webforge\Common\String as S;
use Psc\A;
use Psc\PSC;
use Psc\CMS\RequestMeta;

class Template extends \Psc\SimpleObject implements \Psc\HTML\HTMLInterface, \Psc\CMS\Item\TabOpenable {
  
  /**
   * Übersetzungs Array
   *
   * @var array erstes level ist $language danach kommt ein Flacher array von keys
   */
  protected $i18n;
  
  /**
   * @var string
   */
  protected $language = 'de';
  
  /**
   * @var string
   */
  protected $fileName = NULL;
  
  /**
   * Die Datei in der das Template liegt
   * 
   * ist erst nach validate() gesetzt
   * @var Webforge\Common\System\File
   */
  protected $file = NULL;
  
  /**
   * @var string mit . davor
   */
  protected $extension = '.html';
  
  /**
   * Die Variablen des Templates
   */
  protected $vars = array();
  
  /**
   * Indented das Template
   * 
   * @var int|NULL 0-basierend
   */
  protected $indent = NULL;
  
  public function __construct($fileName, Array $vars = array(), $tabLabel = NULL) {
    $this->setFileName($fileName);
    $this->vars = $vars;
    $this->tabLabel = $tabLabel ?: $this->file->getName(File::WITHOUT_EXTENSION);
  }
  
  /**
   * Übersetzt den angegeben key
   *
   * dieser muss in $this->i18n[$this->language][$key]  definiert sein (sonst gibt es einen notice)
   * @return string
   */
  public function __($key) {
    return $this->i18n[$this->getLanguage()][$key];
  }

  /**
   * Übersetzt den Key und gibt ihn direkt aus
   */
  public function _e($key) {
    print $this->__($key);
  }
  
  /**
   * @param string|array $fileName wenn ein array wird de letzte part als filename und die ersten als subverzeichnisse für tpl/ genommen
   */
  public function setFileName($fileName) {
    $this->fileName = $fileName;
    $this->validate();
  }

  /**
   * Überprüft die Sicherheit des Templates
   *
   * z.b. darf keine Datei includiert werden die außerhalb des tpl Verzeichnisses liegt ($this->getDirectory())
   * @return bool
   */
  public function validate() {
    if (!isset($this->fileName)) {
      throw new Exception('fileName muss gesetzt sein');
    }
    
    $file = (string) $this->getDirectory().implode(DIRECTORY_SEPARATOR,(array) $this->fileName).$this->extension;
    $file = new File($file);
    
    if (!$file->getDirectory()->isSubdirectoryOf($this->getDirectory()) && !$file->getDirectory()->equals($this->getDirectory())) {
      throw new Exception('Security: '.$file->getDirectory().' ist kein Unterverzeichnis von: '.$this->getDirectory());
    }
    
    $this->file = $file;
    
    return TRUE;
  }
  
  /**
   * Gibt das Template als String zurück
   *
   * @return string
   */
  public function get() {
    $__html = NULL;
    
    if ($this->validate()) {
    
      /* dies ersetzt variablen die durch das template verändert werden können: $$__varName */
      extract((array)$this->vars);
      
      $tpl = $this;

      /* get as String */
      try {
        ob_start();
        require $this->file;
        $__html = ob_get_contents();
        ob_end_clean();
      } catch (\Psc\Exception $e) {
        $e->setMessage(sprintf('in TPL(%s): %s',$this->getDisplayName(),$e->getMessage()));
        throw $e;
      }
  
      /* testen ob noch variablen übrig sind */
      //if (DEV) {
      //  $__matches = array();
      //  if (preg_match_all('/\{\$([a-zA-Z_0-9]*)\}/',$__html,$__matches,PREG_SET_ORDER) > 0) {
      //    foreach ($__matches as $__match) {
      //    //throw new Exception('in Template: '.$__fileName.' ist Variable "'.$__match[1].'" nicht definiert');
      //      trigger_error('in Template: '.$__fileName.' wird die Variable "'.$__match[1].'" nicht definiert (die von einem anderen Template benötigt wird)',E_USER_WARNING);
      //    }
      //  }
      //}
  
      if ($this->indent !== NULL) {
        $__html = S::indent($__html, $this->indent);
      }
    }

    return $__html;
  }
  
  /**
   * @return string
   */
  public function getDisplayName() {
    return $this->file->getURL($this->getDirectory());
  }
  
  /**
   * @param string $name
   * @param mixed $value
   */
  public function setVar($name, $value) {
    $this->vars[$name] = $value;
    return $this;
  }
  
  /**
   * @param Traversable
   */
  public function setVars($vars) {
    $this->vars = (array) $vars;
    return $this;
  }
  
  public function getVars() {
    return $this->vars;
  }
  
  /**
   * @return Webforge\Common\System\Directory
   */
  public function getDirectory() {
    return PSC::get(PSC::PATH_TPL);
  }
  
  /**
   * Gibt alle assigned Variables des Templates aus
   *
   * kann im template mit $this->debug() aufgerufen werden
   */
  public function debug() {
    print_r(A::keys((array)$this->vars));
  }
  
  /**
   * @param string $language
   * @chainable
   */
  public function setLanguage($language) {
    $this->language = $language;
    return $this;
  }

  /**
   * @return string
   */
  public function getLanguage() {
    return $this->language;
  }

  /**
   * @return Webforge\Common\System\File
   */
  public function getFile() {
    return $this->file;
  }
  
  /**
   * @param array $i18n
   * @chainable
   */
  public function setI18n(Array $i18n) {
    $this->i18n = $i18n;
    return $this;
  }

  /**
   * @return array
   */
  public function getI18n() {
    return $this->i18n;
  }
  
  public function html() {
    return $this->get();
  }
  
  public function getTabRequestMeta() {
    // siehe auch cms::createTemplateTabLink
    return new RequestMeta(\Psc\Net\HTTP\Request::GET,
                           '/cms/tpl/'.implode('/', (array) $this->fileName)
                           );
  }
  
  /**
   * @param string $tabLabel
   * @chainable
   */
  public function setTabLabel($tabLabel) {
    $this->tabLabel = $tabLabel;
    return $this;
  }

  /**
   * @return string
   */
  public function getTabLabel() {
    return $this->tabLabel;
  }
}
?>