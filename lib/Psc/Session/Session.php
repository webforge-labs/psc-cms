<?php

namespace Psc\Session;

use Psc\Form\DataInput;
use Psc\Exception;

class Session extends \Psc\OptionsObject implements \Webforge\Common\Session {
  
  /**
   * @var Session
   */
  protected static $instance;
  
  /**
   * @var \FormDataInput
   */
  protected $data;
  
  /**
   * Ist true wenn init() schon aufgerufen wurde
   */
  protected $init = FALSE;
  
  public function __construct() {
    $this->setDefaultOptions(Array(
      'cookieDomain'=>isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : NULL,
      'name'=>'SID',
      'cookies'=>TRUE
    ));
  }
  
  /**
   * @return Session
   */
  public static function instance() {
    if (!isset(self::$instance)) {
      self::$instance = new static();
    }
    return self::$instance;
  }
  
  public function get() {
    $keys = func_get_args();
    return $this->data->getDataWithKeys($keys, DataInput::RETURN_NULL);
  }
  
  public function set() {
    $keys = func_get_args();
    $value = array_pop($keys);
    return $this->data->setDataWithKeys($keys, $value);
  }
  
  public function init() {
    if (!$this->init) {
      ini_set('session.name', $this->getOption('name'));
      ini_set("session.use_cookies", (int) $this->getOption('cookies'));
    
      session_start();
      $this->init = TRUE;
      
      // dies hier muss nach session_start gemacht werden, damit die richtige Referenz weitergegeben wird
      $this->data = new DataInput($_SESSION, DataInput::TYPE_ARRAY);
    }
    return $this;
  }
  
  /**
   * @return string
   */
  public function getCookieDomain() {
    return $this->getOption('cookieDomain');
  }
  
  public function destroy() {
    if (session_id() == '') {
      throw new Exception('Session ist nicht initialisiert!');
    }
    
    @session_destroy();
    return $this;
  }
  
  /**
   * z. B. für Test-Methoden
   */
  public static function resetInstance() {
    self::$instance = NULL;
  }
}
