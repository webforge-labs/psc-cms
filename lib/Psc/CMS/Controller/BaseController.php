<?php

namespace Psc\CMS\Controller;

use Psc\Form\DataInput,
    Psc\Code\Code,
    Psc\URL\Helper as URLHelper
;

/**
 * Dieser Controller kann nur:
 *
 * TODO (validiert, defaultTodo, braucht dafür die Map, todoAlias)
 * - zu beachten ist, dass setTodo silent failed, wenn todo ein falscher Parameter ist (und noch nicht mit add() hinzugefügt wurde), dies wurde gemacht um setTodo() sehr Low-Level zu halten, aber trotzdem keine inkonsistenten Stände zu verursachen
 * 
 * initialisiert die VARS
 * Vars ist jetzt doch ein Array. Am Array ist ja nur nervig, dass er so schwierig zu testen ist, ob etwas gesetzt ist für die ableitenden Controller sollen aber diese Arbeit des Überprüfens übernehmen und somit saubere vars arrays erstellen
 *
 * der BaseController ist also nur ein Container für vars, der eine getypte Variable TODO verwaltet.
 *
 * @TODO Zusätzlich hat er Hilfsfunktionen für das Event-System
 * 
 */
abstract class BaseController extends \Psc\OptionsObject implements Controller {
  
  const RETURN_NULL = DataInput::RETURN_NULL;
  const THROW_EXCEPTION = DataInput::THROW_EXCEPTION;
  
  const OPTIONAL = \Psc\Form\Validator::OPTIONAL;

  /**
   * @var const|NULL
   */
  public $todo;
  
  /**
   * @var const|NULL
   */
  protected $defaultTodo = NULL;
  
  /**
   * @var array keys als Strings const TODO_ als Wert
   * @see initTodoMap(), addTodo, resetTodos
   */
  protected $todoMap = array();
  
  
  /**
   * @var array()
   */
  protected $vars = array();
  
  public function __construct() {
    $this->vars = array();
  }
  
  /**
   * @return bool
   */
  public function validateTodo($todo) {
    return array_key_exists($todo,
                            $this->todoMap
                           );
  }

  /**
   * Wenn kein Parameter übergeben wird gibt es den aktuellen TODO zurück
   * wenn gesetzt wird die Konstante für den todoString zurückgegeben
   * @param string $todoString
   * @return const
   */
  public function getTodo($todoAlias = NULL) {
    if ($todoAlias === NULL) return $this->todo;
    
    if (array_key_exists($todoAlias,$this->todoMap)) {
      return $this->todoMap[$todoAlias];
    } else {
      throw new InvalidTodoException('Kein Alias für '.Code::varInfo($todoAlias).' gefunden. Ist dieser String ein Schlüssel in der todoMap?');
    }
  }
  
  /**
   * @inheritdoc
   */
  public function addTodo($todo, $todoAlias = NULL) {
    if (!isset($todoAlias))
      $todoAlias = $todo;
    
    $this->todoMap[$todoAlias] = $todo;
    return $this;
  }

  
  public function addTodos($todos) {
    foreach((array) $todos as $todoAlias => $todo) {
      if (!is_numeric($todoAlias)) {
        $this->addTodo($todoAlias, $todo);
      } else {
        $this->addTodo($todo);
      }
    }
    return $this;
  }
  
  /**
   * @inheritdoc
   * setzt die Variable nicht, wenn validateTodo FALSE zurückgibt
   */
  public function setTodo($todo) {
    if ($this->validateTodo($todo))
      $this->todo = $this->todoMap[$todo];
      
    return $this;
  }
  
  /**
   * Löscht alle gesetzen Werte für die Todo-Map
   */
  public function resetTodos() {
    $this->todoMap = array();
    return $this;
  }
  
  /**
   * @param string $todoString nicht die Konstante sondern 'del' oder so
   */
  public function deleteTodo($todo) {
    if (isset($this->todoMap[$todo]))
      unset($this->todoMap[$todo]);
      
    return $this;
  }

  /**
   * Wird aufgerufen wenn der Controller in einem undefinierten Zustand ist
   */
  public function createInconsistenceException($msg = NULL) {
    throw new InconsistenceException($msg);
  }
  
  /* HilfsFunktionen */
  
  public function trigger($e) {
    //throw new \Psc\Exception('Event noch nicht implementiert');
  }
  

  /**
   * @param string $todo ist dies nicht gesetzt wird das aktuell gesetzte todo genommen
   * @return array
   */
  public function getTodoQueryVars($todo = NULL) {
    if (!isset($todo))
      $todo = $this->getTodo();
    
    $qv = array();
    
    if ($todo != NULL) {
      $qv['todo'] = $todo;
    }
    
    return $qv;
  }
  
  /**
   * Gibt die URL für eine Action des Controllers zurück
   *
   * dies kann z.b. für form action="" benutzt werden
   * @param const $todo wenn NULL wird dies die aktuelle Action/ Default Action
   */
  public function getTodoURL($todo=NULL, $flags = 0x000000) {
    return $this->getURL($this->getTodoQueryVars($todo),$flags);
  }
  
  protected function getURL(Array $queryVars = array(), $flags = 0x000000) {
    $flags |= URLHelper::MERGE_QUERY_VARS;
    return URLHelper::getRelative($flags, $queryVars);
  }

  /**
   * Führt einen Redirect aus
   * 
   */
  protected function redirect($location) {
    header('Location: '.$location);
    exit;
  }
  
  /**
   * Überprüft das Todo (oder das im Objekt gesetzte) und schmeisst eine invalidTodoException, wenn ddas todo gesetzt + falsch ist
   */
  protected function assertTodo($todo = NULL) {
    if (func_num_args() == 0) {
      $todo = $this->todo;
    }
    
    if ($todo != NULL && !$this->validateTodo($todo)) {
      throw new InvalidTodoException('Todo: '.Code::varInfo($todo).' ist nicht in der todoMap');
    }
  }
}

?>