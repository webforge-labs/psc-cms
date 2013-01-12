<?php

namespace Psc\CMS\Controller;

/**
 * Die Reihenfolge ist
 * __construct()
 * init()
 * run()
 */
interface Controller {
  
  const MODE_NORMAL = 0xFFFFFF;
  
  const INIT_INPUT            = 0x000010; // NELD, Auth
  const INIT_TODO             = 0x000020;
  
  const INIT_AJAX_DATA        = 0x000040; // guess
  const INIT_AJAX_CONTROLLER  = 0x000080; // TabsContentItemController
  
  const INIT_OBJECT           = 0x000100; // AceController

  
  /* ein paar TODO-Konstanten (geht auch ohne) */
  const TODO_EDIT = 'edit';
  const TODO_NEW = 'new';
  const TODO_DELETE = 'del';
  const TODO_LIST = 'list';
  const TODO_FORM = 'form';
  const TODO_TABS_CONTENT = 'content';
  const TODO_TABS_INFO = 'info';
  const TODO_LOGIN = 'login';
  const TODO_LOGOUT = 'logout';
  
  /**
   * Die Init Funktion
   *
   * @chainable
   */
  public function init($mode = Controller::MODE_NORMAL);
  
  /**
   * Erstellt das Objekt
   *
   * Wird vom Konstruktor oder sonstwo aufgerufen.
   * Ist anders als init() nicht dafür gedacht Daten zu validieren. Dies passiert in init()
   * 
   * dies ist nicht mit __construct() zu verwechseln. Wir machen dies, damit wir den __construct() nicht definieren und jedem Controller überlassen, was er mit dem Konstruktor tun will
   * ganz wichtig ist, dass bevor irgendwas mit dem Objekt passiert, dass diese Methode des Parents aufgerufen wird
   * Dies setzt z. B. $this->vars im BaseController
   * @chainable
   */
  //public function construct();
  
  
  /**
   * Führt den Controller aus
   * 
   * @chainable
   */
  public function run();
  
  /**
   * Gibt das aktuelle Todo zurück
   * 
   * @return string
   */
  public function getTodo();

  /**
   * Setzt das aktuelle Todo
   *
   * @param string $todo kann auch ein alias sein
   * @chainable
   */
  public function setTodo($todo);
    
  /**
   * Fügt ein Todo (mit Alias) dem Controller hinzu
   * 
   * @param string $todo der String wie er von getTodo() zurückgegeben wird
   * @param string $todoAlias ein Alias für $todo, wird dieser gesetzt gibt getTodo() dann ebenfalls $todo zurück
   * @chainable
   */
  public function addTodo($todo, $todoAlias = NULL);
  
  /**
   * Fügt mehrere Todos dem Controller hinzu
   *
   * Sind die Schlüssel des arrays $todos nicht numerisch wird der schlüssel als alias für den Wert des Arrays verstanden
   * 
   * @param mixed $todos kann ein array sein oder etwas, was man in einen array casten kann, schlüssel sind todoAlias wenn nicht numerisch
   * @chainable
   */
  public function addTodos($todos);
}

/* Alle Exceptions vom Controller */
class Exception extends \Psc\Exception {}

/* Exceptions mit abuse oder Programmierer Fehler */
class SystemException extends Exception {}

/* Systemfehler mit abuse oder Programmierfehler */
class InvalidTodoException extends SystemException {}
class InconsistenceException extends SystemException {}
class NoIdException extends SystemException {}
class NoObjectException extends SystemException {}
?>