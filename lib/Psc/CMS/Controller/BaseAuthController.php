<?php

namespace Psc\CMS\Controller;

use Psc\CMS\Auth,
    Psc\CMS\AuthException
;

abstract class BaseAuthController extends GPCController {
  
  /**
   * @var Auth
   */
  protected $auth;
  
  /**
   * @var mixed
   */
  protected $ident;
  
  /**
   * Das Passwort aus dem Formular
   *
   * Ist in diesem Falle mal ein cleartextPassword
   * @var string
   */
  protected $password;
  
  /**
   * @var bool
   */
  protected $permanent = FALSE;
  
  public function __construct(Auth $auth = NULL) {
    parent::__construct('auth');
    
    $this->auth = $auth ?: new Auth();

    $this->addTodo('login',self::TODO_LOGIN);
    $this->addTodo('logout',self::TODO_LOGOUT);
    $this->init();
  }
  
  
  public function login($ident, $cleartextPassword, $permanent = FALSE) {
    try {
      $ret = $this->auth->login($ident, $cleartextPassword, $permanent);
    
      return $ret;
    } catch (AuthException $e) {
      return $this->processAuthException($e); // auch validate steigt hier damit aus
    }
  }
  
  public function logout($ident) {
    return $this->auth->logout($ident);
  }
  
  public function loginform($ident = NULL, $permanent = FALSE) {
    throw new \Psc\Exception('Bitte loginform() ableiten und implementieren');
  }
  
  public function init($mode = Controller::MODE_NORMAL) {
    parent::init($mode);
    if ($mode & self::INIT_INPUT) $this->initInput();
    
    return $this;
  }
  
  public function initInput() {
    if ($this->todo == self::TODO_LOGIN) {
      $this->ident = $this->getVar('ident');
      $this->password = $this->getVar('password');
      $this->permanent = $this->getVarCheckboxBool('permanent');
    }
    
    if ($this->todo == self::TODO_LOGOUT) {
      $this->ident = $this->getVar('ident');
    }
  }
  
  /**
   * wir überschreiben die Methode, da diese sonst exception schmeiss, wenn wir bei einem fremden Controller-Aufruf initialisieren
   *
   * unser Controller soll ja nur auf login und logout hören und so
   */
  public function initTodo() {
    $todo = $this->getVar(array('todo'),'GP');
    
    $this->setTodo($todo); // passiert nix, wenn es nicht valid is
  }
  
  public function run() {
    if ($this->todo == self::TODO_LOGIN) {
      $this->login($this->ident, $this->password, $this->permanent);
    }
    
    if ($this->todo == self::TODO_LOGOUT) {
      return $this->logout($this->ident); // aussteigen
    }
    
    return $this->validate(); // validieren
  }
  
  abstract public function validate();
  
  protected function processAuthException(AuthException $e) {
    throw $e;
  }
}
