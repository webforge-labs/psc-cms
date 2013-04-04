<?php

namespace Psc\CMS\Controller;

use \Psc\FE\Errors,
    \Psc\CMS\AuthException,
    \Psc\CMS\Auth,
    \Psc\PSC,
    \Psc\TPL\TPL
;

/**
 * Eigentlich wie der BaseAuthController nur, dass dieser hier redirected und Errors verarbeiten kann
 */
class AuthController extends BaseAuthController {
  
  /**
   * @var FEErrors
   */
  protected $errors;
  
  /**
   * Wenn gesetzt wird nach login und logout auf die Seite von redirect redirected (hihi)
   */
  protected $redirect;
  
  /**
   * wenn gesetzt wird diese Seite genommen anstatt der Psc\CMS\HTMLPage (die als standard ganz gut ist)
   */
  protected $htmlPage;

  public function __construct(Auth $auth = NULL, Errors $errors = NULL) {
    parent::__construct($auth);
    
    $this->errors = $errors ?: new Errors();
  }
  
  public function validate() {
    try {
      $this->auth->validate();
      
      return TRUE;
    
    } catch (AuthException $e) {
      if (PSC::inProduction()) {
        //file_put_contents('/tmp/log-logfile',(string) $e,FILE_APPEND);
      }
      
      $this->processAuthException($e);
      
      return FALSE;
    }
  }
  
  public function loginform($ident = NULL, $permanent = FALSE) {
    $html = $this->htmlPage ?: new \Psc\CMS\HTMLPage();
    
    $formAction = $this->getTodoURL(self::TODO_LOGIN);
    $redirect = $this->getRedirect();
    
    $errors = $this->errors;
    
    $html->body->content = TPL::get(array('CMS','loginform'), compact('formAction','errors','ident','permanent','html','redirect'));
    
    print $html->getHTML();
  }
  
  public function run() {
    if (parent::run() === TRUE) { // validiert usw
    
      // zur Seite gehen 
      if ($this->todo == self::TODO_LOGIN || $this->todo == self::TODO_LOGOUT) {
        $this->redirect = $this->getVarDefault('redirect', $this->redirect);
      
        if (isset($this->redirect)) { // und redirecten zu der nächsten seite
          $this->redirect($this->redirect);
        }
      }
    } else {
      /* einloggen */
      $this->loginform($this->ident,$this->permanent);
      exit;
    }
  }
  
  protected function processAuthException(AuthException $e) {
    if (isset($this->errors)) {
      
      if ($this->todo == self::TODO_LOGIN) 
        $this->errors->addEx($e);
        
    } else {
      throw $e;
    }
  }
  
  public function setRedirect($url) {
    $this->redirect = $url;
    return $this;
  }
  
  public function getRedirect() {
    return $this->redirect;
  }
  
  /**
   * @param string FQN
   */
  public function setUserClass($class) {
    $this->getAuth()->setUserClass($class);
    return $this;
  }
  
  /**
   * @return Psc\CMS\User
   */
  public function getUser() {
    return $this->getAuth()->getUser();
  }
  
  /**
   * @return \Psc\CMS\Auth
   */
  public function getAuth() {
    return $this->auth;
  }

  public function setHTMLPage(\Psc\HTML\Page $page) {
    $this->htmlPage = $page;
    return $this;
  }
}
