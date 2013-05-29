<?php

namespace Psc\CMS;

use Psc\Session\Session;
use Psc\GPC;
use Psc\Code\Code;
use Psc\PHP\CookieManager;
use Psc\PSC;

class Auth extends \Psc\Object {
  
  /**
   * @var Auth
   */
  protected static $instance;
  
  /**
   * @var \Psc\Session\Session
   */
  protected $session;
  
  /**
   * @var Psc\CMS\UserManager
   */
  protected $userManager;

  /**
   * Der User der eingeloggt ist
   *
   * @var \Psc\CMS\User|NULL wenn nicht eingeloggt, ist dies hier NULL
   */
  protected $user;
  
  /**
   * @var string eine Klasse die Psc\CMS\User ableitet
   */
  protected $userClass = 'Psc\CMS\User';
  
  /**
   * @var CookieManager
   */
  protected $cookieManager;
  
  protected $debug = FALSE;
  
  public function __construct(Session $session = NULL, CookieManager $cookieManager = NULL, UserManager $userManager = NULL) {
    if ($session == NULL) {
      $session = Session::instance();
    }
    
    $this->session = $session;
    $this->session->init(); // sichergehen, dass initialisiert wird

    $this->cookieManager = $cookieManager ?: new CookieManager();
    $this->cookieManager->setDomain($this->session->getOption('cookieDomain'));
    
    $this->userManager = $userManager; // getter injection
  }
  
  /**
   * @return Session
   */
  public static function instance(Session $session = NULL) {
    if (!isset(self::$instance)) {
      self::$instance = new \Psc\CMS\Auth($session);
    }
    
    return self::$instance;
  }
  
  /**
   * @param mixed $ident der Identifier des Users. Wird der input für User::retrieveByIdentifier()
   * @param string $plaintextPassword das Passwort im Klartext (z. B. aus dem Login-Formular)
   * @param bool $permanent speichert die Login Information als Cookie
   */
  public function login($ident, $plaintextPassword, $permanent = FALSE) {
    // @TODO das hier geht nicht lange gut, eigentlich wäre hier eine user instance (die wir aber erst bei validate() bekommen, viel schöner)
    $password = ($plaintextPassword != '') ? md5($plaintextPassword) : NULL;
    
    $this->session->set('user','ident',$ident);
    $this->session->set('user','password',$password); 
    $this->session->set('user','ctime',time());
    $this->session->set('user','mtime',time());
    
    if ($permanent) {
      $this->cookieManager->set('login',array('ident'=>$ident, 'password'=>$password));
    }
  }
  
  /**
   * Löscht das Cookie und zerstört die Session
   */
  public function logout() {
    $this->cookieManager->del('login');
    $this->session->destroy();
    return $this;
  }
  
  public function reset() {
    $this->session->set('user','ident',NULL);
    $this->session->set('user','password',NULL);
  }
  
  /**
   *
   * _POST[user][ident]
   * _POST[user][password]
   * können in Formularen angegeben werden
   *
   * @throws NoAuthException, NoUserException, WrongPasswordException
   */
  public function validate() {
    $cleartextPassword = $password = NULL;
    
    $cookie['ident'] = $cookie['password'] = NULL; // wegen notice
    
    if (($ident = $this->session->get('user','ident')) != '') {
      $password = $this->session->get('user','password');
    } elseif (is_array($cookie = $this->cookieManager->get('login'))) {
      $ident = $cookie['ident'];
      $password = $cookie['password'];
    } elseif (($ident = GPC::POST('ident')) != '') {
      $cleartextPassword = GPC::POST('password');
    } elseif(isset($_SERVER['PHP_AUTH_USER'])) {
      $ident = $_SERVER['PHP_AUTH_USER'];
      $cleartextPassword = $_SERVER['PHP_AUTH_PW'];
    }
    
    $debugString = $this->debug ? sprintf("\nsession[ident,pw] = '%s','%s'\n".
                                   "cookie[ident,pw] = '%s','%s'\n".
                                   "POST[ident,pw] = '%s','%s'\n".
                                   "HTTP[ident,pw] = '%s','%s'\n",
                                   $this->session->get('user','ident'), $this->session->get('user','password'),
                                   $cookie['ident'],$cookie['password'],
                                   GPC::POST('ident'),GPC::POST('password'),
                                   @$_SERVER['PHP_AUTH_USER'],@$_SERVER['PHP_AUTH_PW']
                                  ) : NULL;
    
    try {
      if (empty($ident) || (empty($password) && empty($cleartextPassword))) {
        throw new NoAuthException(
          'Es sind keine Credentials angegeben / gespeichert. '.$debugString
        );
      } else {
      
        /* wir versuchen den User zu laden */
        try {
          $user = $this->getUserManager()->get($ident);
        } catch (NoUserException $e) {
          throw $e->setMessage($e->getMessage().' '.$debugString);
        }
      
        /* Wir vergleichen das Passwort */
        $equals = isset($cleartextPassword) ? $user->passwordEquals($cleartextPassword) : $user->hashedPasswordEquals($password);
      
        if (!$equals) {
          throw new WrongPasswordException('Passwort ist nicht korrekt. '.$debugString);
        }
        
        $this->user = $user;
      }
      
    } catch (AuthException $e) {
      /* failure */
      
      $this->cookieManager->del('login');
      $this->reset();
      
      throw $e;
    }
  }
  
    /**
   * @return string
   */
  public function generatePassword() {
    $chars = "abcdefghijkmnopqrstuvwxyz023456789";

    $i = 1;
    $pass = '' ;
    while ($i <= self::$passwordLength) {
      $num = rand() % 33;
      $pass .= mb_substr($chars, $num, 1);
      $i++;
    }

    return $pass;
  }
  
  /**
   * z. B. für Test-Methoden
   */
  public static function resetInstance() {
    self::$instance = NULL;
  }
  
  public function getUserManager() {
    if (!isset($this->userManager)) {
      $this->userManager = new UserManager(PSC::getProject()->getModule('Doctrine')->getRepository($this->userClass));
    }
    return $this->userManager;
  }
  
  /**
   * @param Psc\CMS\User $user
   * @chainable
   */
  public function setUser(\Psc\CMS\User $user) {
    $this->user = $user;
    return $this;
  }

  /**
   * @return Psc\CMS\User
   */
  public function getUser() {
    return $this->user;
  }
  
  public function getUserClass() {
    return $this->userClass;
  }
  
  /**
   * @param bool $debug
   * @chainable
   */
  public function setDebug($debug) {
    $this->debug = $debug;
    return $this;
  }

  /**
   * @return bool
   */
  public function getDebug() {
    return $this->debug;
  }
}
