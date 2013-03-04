<?php

namespace Psc\JS;

use Psc\TPL\TPL;
use stdClass;

class Snippet extends \Psc\SimpleObject implements Expression, \Psc\HTML\HTMLInterface {
  
  const MAIN = 'main';
  
  /**
   * Spezielle Variable für das Objekt auf welches sich das Snippet bezieht
   *
   */
  const VAR_SELF = '%self%';
  const VAR_NAME_SELF = 'self';
  
  /**
   * Werden in $code ersetzt wenn sie mit %$schlüssel% markiert sind
   * @var object
   */
  protected $vars;
  
  /**
   * @var string|array
   */
  protected $code;
  
  /**
   * @var bool
   */
  protected $onPscReady = FALSE;
  
  /**
   * @var array Strings von Klassennamen
   */
  protected $use = array();
  
  
  protected $requirementsAliases;
  
  public function __construct($code, Array $vars = array()) {
    $this->code = $code;
    $this->vars = (object) $vars;
    $this->requirementsAliases = array();
  }
  
  /**
   * @return string
   */
  public function JS() {
    $code = $this->getCode();
    
    if (is_string($code)) {
      $code = array($code); // blöde interfaces vom js helper
    } elseif ($code instanceof \Psc\JS\Expression) {
      $code = array($code->JS());
    }

    // load-wrappers (von innen nach außen)
    if ($this->onPscReady === 'main') {
      // non blocking mode (is save to use in html inline scripts directly)
      $this->unshiftRequirement('app/main');
      $this->unshiftRequirementAlias('main');
      
      $this->unshiftRequirement('jquery');
      $this->unshiftRequirementAlias('jQuery');
      
      $code = Helper::bootLoad($this->getRequirements(), $this->requirementsAliases, $code);
    } elseif($this->onPscReady) {
      // is only save to use in ajax requests / inline scripts where window.requireLoad is already defined
      $this->unshiftRequirement('jquery');
      $this->unshiftRequirementAlias('jQuery');

      $code = Helper::requireLoad($this->getRequirements(), $this->requirementsAliases, $code);
    } elseif (count($req = $this->getRequirements()) > 0) {
      $code = Helper::requirejs($req, array(), $code);
    } else {
      $code = count($code) > 1 ? \Webforge\Common\ArrayUtil::join($code, "%s\n") : current($code);
    }

    return TPL::miniTemplate((string) $code, (array) $this->vars);
  }
  
  public function html() {
    return Helper::embed($this->JS());
  }
  
  /**
   * Setzt die Dependencies fürs Auto-Loading (des Snippets)
   * 
   * @param array $use
   * @chainable
   */
  public function setUse(Array $use) {
    $this->use = $use;
    return $this;
  }

  /**
   * @return array
   */
  public function getUse() {
    return $this->use;
  }
  
  /**
   * @return array with paths for require() calls
   */
  public function getRequirements() {
    return array_map(function ($class) {
      return str_replace('.', '/', $class);
    }, (array) $this->use);
  }

  public function addRequirementAlias($name) {
    $this->requirementsAliases[] = $name;
    return $this;
  }

  public function unshiftRequirementAlias($name) {
    array_unshift($this->requirementsAliases, $name);
    return $this;
  }
  
  public function addRequirement($req) {
    $this->addUse($req);
    return $this;
  }

  public function unshiftRequirement($req) {
    array_unshift($this->use, $req);
    return $this;
  }

  /**
   * @param string|array
   * @chainable
   */
  public function addUse($use) {
    if (is_array($use)) {
      $this->use = array_unique(array_merge($this->use, $use));
    } else {
      if (!in_array($use, $this->use)) $this->use[] = (string) $use;
    }
    return $this;
  }
  
  /**
   * @param stdClass $vars
   * @chainable
   */
  public function setVars(stdClass $vars) {
    $this->vars = $vars;
    return $this;
  }
  
  public function setVar($name, $value) {
    $this->vars->$name = $value;
    return $this;
  }

  /**
   * @return stdClass
   */
  public function getVars() {
    return $this->vars;
  }
  
  /**
   *
   * use self::MAIN as $load, when you want to load the snippet on the direct html page
   * @param bool|string $load can be TRUE for normal loading and "main" to wait for the main application to start
   * @chainable
   */
  public function loadOnPscReady($load = TRUE) {
    $this->onPscReady = $load;
    return $this;
  }
  
  protected function getCode() {
    return $this->code;
  }

  public function __toString() {
    return (string) $this->html();
  }
  
}
?>