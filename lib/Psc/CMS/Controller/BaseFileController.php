<?php

namespace Psc\CMS\Controller;

use \Webforge\Common\System\Dir,
    \Webforge\Common\System\File,
    \Psc\PSC
;

/**
 * Die zweite Klasse von Controllern
 * 
 * dies ist die erste instantiierbare Klasse der Hierarchie
 * - file und eine Run-Funktion die eine Datei mit dem Namen ctrl.$name.php aus dem File System aufruft
 * - init tut nichts
 *
 * somit kann sie (grob)
 * - TODO
 * - Vars
 * - require File
 *
 * im Standard-Fall liegen die Controller im SRC_PATH ohne Unterverzeichnisse
 */
class BaseFileController extends BaseController {
 
  /**
   * Muss ein ParentDirectory von allen Dateien sein, die inkludiert werden sollen
   * 
   * @var Dir
   */
  protected $directory;
  
  /**
   * Wird nach dem ersten run() erst gesetzt (und validiert)
   *
   * Diese Datei vorher zu setzen hat keinen Effekt!
   * @see getFileName()
   * @var File
   */
  protected $file;
  
  /**
   * @var string der Name des Controllers. ctrl.$name.php also ohne ctrl und php davor und punkte etc
   */
  protected $name;
  
  public function __construct($name) {
    $this->name = $name;
    
    parent::__construct();
  }
  
  public function getFileName() {
    if (empty($this->name)) {
      throw new SystemException('Name muss gesetzt sein.');
    }
    return sprintf('ctrl.%s.php',$this->name);
  }
  
  public function init($mode = Controller::MODE_NORMAL) {
  }
  
  /**
   * Lädt die Controller Datei
   *
   * macht require $this->getFileName()
   * getFileName darf einen relativen Pfad zu $this->getDirectory() zurückgeben
   */
  public function run() {
    $this->trigger('run.before');

    /* wir validieren die Datei */
    $this->file = new File($this->getDirectory(), $this->getFileName());
    
    $fDir = $this->file->getDirectory();
    $incDir = $this->getDirectory();
    
    if (!$this->file->exists() || !$incDir->equals($fDir) && !$fDir->isSubdirectoryOf($incDir)) { // verzeichnis darf nicht tiefer gewechselt werden, als das getDirectory()
      if (!$this->file->exists()) {
        throw new SystemException('Datei: '.$this->file.' wurde nicht gefunden.');  
      } else {
        throw new SystemException('Datei: '.$this->file.' ist nicht in '.$this->getDirectory().' enthalten (security check)');  
      }
      
    }
    unset($fDir,$incDir); // die wollen wir nicht im scope haben
    $this->file->getDirectory()->makeRelativeTo($this->getDirectory()); // das ist eher etwas kosmetik, als Sicherhheit
    
    
    /* Datei includieren */
    extract($this->getExtractVars()); 

    require mb_substr((string) $this->file, 2); // schneidet das ./ oder .\ ab
   
    $this->trigger('run.after');
    return $this;
  }
  
  protected function getExtractVars() {
    // das ist so ganz nett, aber eigentlich nicht nötig,
    // da wir sowieso durch $this->vars darauf zugreifen könnten
    return $this->vars;
  }

  public function getDirectory() {
    if (!isset($this->directory)) {
      $this->directory = PSC::get(PSC::PATH_SRC);
    }
    
    return $this->directory;
  }
}


?>