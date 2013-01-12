<?php

namespace Psc\CMS\Controller;

use \Psc\Form\DataInput AS FormDataInput,
    \Psc\Code\Code
    ;

/**
 * GPC Controller
 *
 * der GPC Controller nimmt daten aus GET und POST (später mal Cookie und Session?) und kann Variablen den vars des Controllers hinzufügen (siehe var - Funktionen)
 * wird in post oder get (Post hat Vorrang) ein Eintrag "todo" gefunden, wird dieser als todo im Objekt gesetzt (bei init)
 *
 * - init mit Todo
 * - get und post als Public Variablen, die auch als $get und $post in der Controller File gesetzt werden
 * - required eine Datei (weil von BaseFileController abgeleitet)
 *
 * get und post sind direkt nach dem Erstellen des Objektes initialisiert
 */
class GPCController extends BaseFileController {
  
  public $get;
  
  public $post;
  
  public function __construct($name) {
    parent::__construct($name);
    
    $this->get = new FormDataInput($_GET);
    $this->post = new FormDataInput($_POST);
  }
  
  public function init($mode = Controller::MODE_NORMAL) {
    parent::init($mode);
    
    if ($mode & Controller::INIT_TODO) $this->initTodo();
    return $this;
  }
  
  /**
   * Initialisiert das Todo des Controllers
   *
   * der Schlüssel 'todo' muss in $this->vars gesetzt sein, damit ein todo aus der Map genommen wird
   * ist der Schlüssel gesetzt, aber der Wert nicht in der todoMap wird eine Exception geworfen
   * ist der Schlüssel nicht gesetzt, wird $this->todo auf $this->defaultTodo gessetzt
   *
   * $_POST['todo'] hat Vorrang vor $_GET['todo']
   * Es wird immer setTodo() mit dem gefundenen Wert aufgerufen
   * @uses validateTodo(), setTodo()
   */
  public function initTodo() {
    $todo = $this->getVar(array('todo'),'GP');
    $this->assertTodo($todo);
    $this->setTodo($todo);
    
    if (!isset($this->todo))
      $this->todo = $this->defaultTodo;
  }

  
  /**
   * Gibt eine Variable zurück
   *
   * die Precendence gibt an welche Variable gewinnt, wenn beide definiert sind
   * @param mixed $keys wenn $keys NULL ist wird angenommen, dass es array($varname) ist
   * @param array $precendence
   * @return mixed|NULL gibt NULL zurück wenn die Variable nicht gesetzt, oder nicht gefunden wurde
   */
  public function getVar($keys, $precendence = 'GP') {
    
    /* Performance Tweak */
    if ($precendence == 'GP') {
      if (($p = $this->post->get($keys, self::RETURN_NULL)) != NULL) return $p;
      if (($g = $this->get->get($keys, self::RETURN_NULL)) != NULL) return $g;
    }
    
    if ($precendence == 'PG') {
      if (($g = $this->get->get($keys, self::RETURN_NULL)) != NULL) return $g;
      if (($p = $this->post->get($keys, self::RETURN_NULL)) != NULL) return $p;
    }
    
    foreach (preg_split('/[a-z]/',$precendence) as $type) {
      switch($type) {
        case 'g': 
        case 'p': 
          $this->$type($keys, self::RETURN_NULL);
        
        case 'c': 
        case 's':
          throw new \Psc\Exception('c und s nocht nicht implementiert');
      }
    }
    
    return NULL;
  }

  /**
   * @return bool
   */
  public function getVarCheckboxBool($keys, $precendence = 'GP') {
    return $this->getVar($keys,$precendence) == 'true';
  }

  /**
   * @return bool
   */
  public function getVarDefault($keys, $default = NULL, $precendence = 'GP') {
    return $this->getVar($keys,$precendence) ?: $default;
  }
  
  /**
   * GET
   * @param array|string $keys, string wird in array umgecastet! es geht also n
   */
  public function g($keys, $do = self::THROW_EXCEPTION) {
    return $this->get->get($keys, $do);
  }
  
  /**
   * POST
   * @param array|string $keys, string wird in array umgecastet! es geht also n
   */
  public function p($keys, $do = self::THROW_EXCEPTION) {
    return $this->post->get($keys, $do);
  }
  
  
  public function gp($keys) {
    return $this->getVar($keys, 'GP');
  }
  
  /* COMPAT: DEPRECATED */
  //public function _GETNULL() {
  //  $keys = func_get_args();
  //  return $this->get->getDataWithKeys($keys, DataInput::RETURN_NULL);
  //}
  //
  //public function _POSTNULL() {
  //  $keys = func_get_args();
  //  return $this->post->getDataWithKeys($keys, DataInput::RETURN_NULL);
  //}
  //
  //public function _GET() {
  //  $keys = func_get_args();
  //  return $this->get->getDataWithKeys($keys, DataInput::THROW_EXCEPTION);
  //}
  //
  //public function _POST() {
  //  $keys = func_get_args();
  //  return $this->post->getDataWithKeys($keys, DataInput::THROW_EXCEPTION);
  //}
  //
  //public function _GETEX() {
  //  $keys = func_get_args();
  //  return $this->get->getDataWithKeys($keys, DataInput::THROW_EXCEPTION);
  //}
  //
  //public function _POSTEX() {
  //  $keys = func_get_args();
  //  return $this->post->getDataWithKeys($keys, DataInput::THROW_EXCEPTION);
  //}
}