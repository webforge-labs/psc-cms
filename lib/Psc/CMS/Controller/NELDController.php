<?php

namespace Psc\CMS\Controller;

use Psc\Code\Code;

/**
 * = Ein Controller für die Standard NELD - Funktionen = 
 *
 * == Allgemein ==
 *
 * N  New
 * E  Edit
 * L  List
 * D  Delete
 *
 * 
 * die quasi in jedem CMS ausgeführt werden müssen.
 * List ist dabei z. B. eine Tabellenansicht der Elemente, von der man eins auswählt. Und dort neue erstellt oder bestehendende editiert.
 * 
 * 'del',self::TODO_DELETE
 * 'delete',self::TODO_DELETE
 * 'edit',self::TODO_EDIT
 * 'list',self::TODO_LIST
 *
 * == Konstructor ==
 * Der Konstruktor ist auffällig klein. Das ist so, damit ableitenden Klassen noch genug Freiraum haben diesen zu Überladen. Von ableitenden Klassen sollte unbedingt gewährleistet werden, dass init() selbst aufgerufen wird
 * Standardmäßig bestimmt der $name im Konstruktor die datei ctrl.$name.php im SRC_PATH - Verzeichnis
 *  
 * == Init ==
 * Ableitende oder aufrufende Klassen können mit der Bitmaske $mode bestimmen, was der Controlle beim aufrufen der init() Funktion alles macht.
 * Wird von diesem nicht gebraucht gemacht, werden alle Funktionen mit dem prefix init* geladen, die im Kontext sind machen.
 * Erweiterene Klassen können die simplen Methoden dann durch komplexe Arbeiten ersetzen
 * Vielen Init-Funktionen werden zur Orientierung Parameter mitgegeben, dies sind aber meist referenzen auf Properties der Klasse,
 *
 * @TODO es sollen Rules eingefügt werden können, wie Objekte gemapped werden oder IDs validiert werden, etc
 * 
 * Defaultmäßig wird hinzugefügt:
 * 
 * === initInput ===
 * wird relativ früh aufgerufen und setzt die Parameter wie id, todo, submitted, saved, etc
 *
 * 
 * == Formular (initFormData) ==
 * Das Formular sollte in initFormData() validiert werden. Dies wird nur ausgeführt, wenn initInput() zu dm
 *
 * == name ==
 * Das Property Name kann als KlassenName von $object benutzt werden. Dies sollte auch nicht viel anders benutzt werden und stellt den "Typ" des Controllers dar
 *
 * == Exceptions ==
 * Controller\Exceptions sind die Exceptions die auftreten, wenn der Programmierer was verbockt
 * wenn die Seite abused widr oder der Programmierer es verbockt werden Controller\SystemExceptions geworfen
 * (z.b. edit ohne id oder delete ohne id, oder datenbank nicht erreichbar etc)
 * in den Controlleraufruf kann deshalb ein try/catch Block für Controller\SystemException gemacht werden
 */
class NELDController extends GPCController {
  
  /**
   * @var mixed
   */
  public $id;

  /**
   *
   * Wenn vars[save] == 'true' ist
   * 
   * @var bool
   */
  public $save;
  public $submitted;
  
  /**
   * Ein Array von Konstanten für die save erlaubt ist
   * 
   * @var array
   */
  public $saveTodos = array(self::TODO_NEW, self::TODO_EDIT, self::TODO_DELETE);
  
  
  /**
   * @var string
   */
  public $name;
  
  public function __construct($name) {
    parent::__construct($name);
    $this->name = $name;

    $this->addTodo(self::TODO_DELETE,'del');
    $this->addTodo(self::TODO_DELETE,'delete');
    $this->addTodo(self::TODO_EDIT,'edit');
    $this->addTodo(self::TODO_LIST,'list');
    $this->addTodo(self::TODO_NEW,'new');
  }
  
  public function init($mode = self::MODE_NORMAL) {
    parent::init($mode);
    
    if ($mode & self::INIT_INPUT) $this->initInput();
  }
  
  /**
   * Setzt alle Variablen nach den Rules für die init-Dinge
   *
   */
  public function initInput() {
    $this->id = NULL;
    $idkey = $this->getIdKey();
    $id = $this->g($idkey,NULL);
    if ($id != NULL && $this->validateId($id)) {
      $this->id = $id;
    }
    
    /* Wenn todo edit, del ist muss id natürlich gesetzt sein */
    if ($this->todo == self::TODO_EDIT || $this->todo == self::TODO_DELETE) {
      if ($this->id == NULL) {
        $this->createNoIdException('Keine valide ID in _GET['.$idkey.'] gefunden. ');
      }
    }
    
    $this->save = $this->p('save',NULL) == 'true';
    try {
      $this->submitted = $this->p('submitted',self::THROW_EXCEPTION) == 'true';
    } catch (\Psc\DataInputException $e) { // wenn es nicht gesetzt ist
      $this->submitted = $this->save;
    }
    
    /* wenn save gesetzt ist, muss edit / delete oder new sein */
    if ($this->save && !(in_array($this->todo,$this->saveTodos))) {
      $this->createInconsistenceException('Es kann nicht save übergeben worden sein, wenn die action nicht '.implode('|',$this->saveTodos).' ist. GET[todo] im Formular vergessen? Wert für Todo: '.Code::varInfo($this->todo));
    }
  }

  /**
   * @return array
   */
  public function getQueryVars() {
    return $this->getTodoQueryVars(NULL);
  }
  
  /**
   *
   * erweitert query Vars mit der id
   * @return array
   */
  public function getTodoQueryVars($todo = NULL, $id = NULL) {
    if (!isset($id)) $id = $this->id;
    
    $qv = parent::getTodoQueryVars($todo);
    
    if ($id) {
      $qv[$this->getIdKey()] = $id;
    }
    
    return $qv;
  }

  /**
   * Gibt den Namen des GET-Schlüssels zurück unter dem die id übergeben wird
   *
   * Default: $this->name.'Id';
   * @return string
   */
  public function getIdKey() {
    return $this->name.'Id';
  }
  
  /**
   * Checkt ob die übergebene ID valid ist
   * 
   * @return bool
   */
  public function validateId($id) {
    return ((int) ($id)) > 0;
  }
  
  public function isEdit() { return $this->todo == self::TODO_EDIT; }
  public function isDelete() { return $this->todo == self::TODO_DELETE; }
  public function isNew() { return $this->todo == self::TODO_NEW; }
  public function isList() { return $this->todo == self::TODO_LIST; }

  /* Exceptions und Fehler */
  
  /**
   * Wird aufgerufen wenn todo set oder edit ist aber die id == NULL ist
   */
  public function createNoIdException($msg = NULL) {
    throw new NoIdException($msg);
  }
}

?>