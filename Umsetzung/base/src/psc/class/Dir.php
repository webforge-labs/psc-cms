<?php
/**
 * 
 * Konventionen: Pfade enden immer mit Trailingslash!
 * Alle PFade werden im Unix-Style angegeben. Sprich D:/www/banane/etc für windows Pfade
 */
class Dir extends Object {

  const SORT_ALPHABETICAL = 2;

  const ORDER_ASC = 1024;
  const ORDER_DESC = 2048;

  const RECURSIVE = 2;


  /**
   * Der Pfad
   * 
   * Wenn das Verzeichnis in Root liegt ist dieser Array einelementig mit / drin
   * @var array alle Unterverzeichnisse als String
   */
  protected $path = array();


  /**
   * Der komplette Pfad zum Verzeichnis
   * 
   * Funktionen die den Pfad verändern, müssen diesen Cacheh hier löschen.<br />
   * der Cache wird von getPath() erstellt.
   * @var string
   * @see getPath()
   */
  protected $pathCache;

  /**
   * Globale Ignores für das Verzeichnis
   * 
   * z.b. betriebssystem spezifischer kram
   * @param array
   */
  public $ignores = array();

  public function __construct($path = NULL) {
    if (isset($path)) {
      $this->setPath($path);
    }
  }

  /**
   * 
   * @param string $path mit trailingslash nur forward-slashes als Verzeichnis-Trenner
   */
  public function setPath($path) {
    $path = trim($path); // whitespace cleanup

    /* erstmal naive */
    if ($this->getOS() == 'WINDOWS') {
      $path = str_replace('\\','/',$path);
    }

    if (!s::endsWith($path,'/'))
      throw new Exception('Verzeichnis Pfad: "'.$path.'" hat keinen Tralingslash');

    $pathArray = array();
    if (mb_strlen($path) > 0) {
      
      if (mb_strpos($path,'/') !== FALSE) {
        $pathArray = explode('/',$path);
      } else {
        $pathArray[] = $path;
      }

      /* leere elemente rausfiltern (safe) */
      $pathArray = array_filter($pathArray,create_function('$a','return (mb_strlen($a) > 0);'));
      $pathArray = array_merge($pathArray); // renumber
      $this->path = $pathArray;
    }
    
    return $this;
  }

  /**
   * Wandelt relative Bezüge im Pfad des Verzeichnis in konkrete um
   *
   * löst z.b. sowas wie ./ oder ../ auf.<br />
   * Kann dafür benutzt werden das aktuelle Verzeichnis als Objekt zu erhalten:
   * <code>
   *   print Dir::factory('.')->resolvePath();
   * </code>
   * @uses PHP::getcwd()
   * @chainable
   */
  public function resolvePath() {
    if (count($this->path) == 0) {
      return $this;
    }
    
    if ($this->path[0] == '.' || $this->path[0] == '..') { 
      /* wir ermitteln das aktuelle working directory und fügen dieses vor unserem bisherigen Pfad hinzu
       * den . am anfang brauchen wir nicht machen, das wird nachher normalisiert
       */
      $cwd = self::factory(str_replace("\\","/",getcwd()).'/');
      $this->path = array_merge(
        $cwd->getPathArray(), 
        $this->path
      ); 
    }

    /* pfad normalisieren */
    $newPath = array();
    foreach ($this->path as $dir) {
      if ($dir !== '.') { // dir2/dir1/./dir4/dir3 den . ignorieren
        if ($dir == '..') {  // ../ auflösen dadurch, dass wir ein verzeichnis zurückgehen
          array_pop($newPath);
        } else {
          $newPath[] = $dir;
        }
      }
    }
        
    $this->path = $newPath;

    return $this;
  }


  /**
   * Verkürzt einen Pfad in Bezug auf ein anderes Verzeichnis
   * 
   * Die Funktion kann z.b. dafür benutzt zu werden aus einem absoluten Verzeichnis ein Relatives zu machen.<br />
   * Die Umwandlung in ein relatives Verzeichnis geschieht in Bezug auf das angegebene Verzeichnis.<br />
   * Wenn das aktuelle Verzeichnis ein Unterverzeichnis des angegebenen ist, wird das Verzeichnis in ein relatives 
   * umgewandelt (sofern es das nicht schon war) und der Pfad bis zum angegeben Verzeichnis verkürzt.
   * @param Dir $dir das Verzeichnis zu welchem Bezug genommen werden soll
   */
  public function makeRelativeTo(Dir $dir) {
    $dir = clone $dir;
    $removePath = (string) $dir->resolvePath();
    $thisPath = (string) $this->resolvePath();
    
    if (!string::starts_with($thisPath,$removePath) || mb_strlen($thisPath) < mb_strlen($removePath))
      throw new Exception('dieses Verzeichnis ('.$thisPath.') muss ein Unterverzeichnis des angegebenen Verzeichnisses ('.$removePath.') sein');

    if ($removePath == $thisPath) {
      $this->setPath('.'); // das Verzeichnis ist relativ gesehen zu sich selbst das aktuelle Verzeichnis
      return $this;
    }

    /* schneidet den zu entfernen pfad vom aktuellen ab */
    $rmvCnt = count($dir->getPathArray());
    for ($i = 1; $i<=$rmvCnt; $i++) {
      array_shift($this->path);
    }
    
    /* ./ hinzufügen */
    array_unshift($this->path,'.');

    return $this;
  }

  /**
   * Fügt dem aktuellen Verzeichnis-Pfad ein Unterverzeichnis oder mehrere (einen Pfad) hinzu
   * 
   * Das angegebene Verzeichnis ist ein relatives Verzeichnis und dessen Pfad wird hinzugefügt
   * $dir->append('subdir/');
   * $dir->append('./banane/tanne/apfel/');
   * @param string|Dir $dir das Verzeichnis muss relativ sein
   * @chainable
   */
  public function append($dir) {
    if (is_string($dir)) {
      if (!s::endsWith($dir,'/')) $dir .= '/';
      
      $dir = new Dir($dir);
    }

    foreach ($dir->getPathArray() as $part) {
      if ($part == '.') continue;
      $this->path[] = $part;
    }
    return $this;
  }


  public function clone_() {
    return clone $this;
  }

  /**
   * Gibt einen Array über die Verzeichnisse und Datein im Verzeichnis zurück
   * 
   * Ignores:<br />
   * bei den Ignores gibt es ein Paar Dinge zu beachten: Es ist zu beachten, dass strings in echte Reguläre Ausdrücke umgewandelt werden. Die Delimiter für die Ausdrücke sind // 
   * Der reguläre Ausdruck wird mit ^ und $ ergänzt. D.h. gibt man als Array Eintrag '.svn' wird er umgewandelt in den Ausdruck '/^\.svn$/' besondere Zeichen werden gequotet
   * Wird der Delimiter / am Anfang und Ende angegeben, werden diese Modifikationen nicht gemacht<br />
   * Diese Ignore Funktion ist nicht mit Wildcards zu verwechseln (diese haben in Regulären Ausdrücken andere Funktionen).
   * 
   * Ignores von unserem Verzeichnis werden an die Unterverzeichnisse weitervererbt.
   *
   * Extensions: <br />
   * Wird extensions angegeben (als array oder string) werden nur Dateien (keine Verzeichnisse) mit dieser/n Endungen in den Array gepackt.
   * Ignores werden trotzdem angewandt.
   * 
   * @param array|string $extensions ein Array von Dateiendungen oder eine einzelne Dateiendung
   * @param array $ignores ein Array von Regulären Ausdrücken, die auf den Dateinamen/Verzeichnisnamen (ohne den kompletten Pfad) angewandt werden
   * @param int $sort eine Konstante die bestimmt, wie die Dateien in Verzeichnissen sortiert ausgegeben werden sollen
   * @return Array mit Dir und File
   */
  public function getContents($extensions = NULL, Array $ignores=NULL, $sort = NULL) {
    if (!$this->exists())
      throw new Exception('Verzeichnis existiert nicht: '.$this);

    $handle = opendir((string) $this);
      
    if ($handle === FALSE) {
      throw new Exception('Fehler beim öffnen des Verzeichnisses mit opendir(). '.$this);
    }

    /* ignore Dirs schreiben */
    if (isset($this->ignores) || $ignores != NULL) {
      $ignores = array_merge($this->ignores, (array) $ignores);

      foreach ($ignores as $key=>$ignore) {
        if (!s::startsWith($ignore,'/') || !s::endsWith($ignore,'/'))
          $ignore = '/^'.$ignore.'$/';
          
        $ignores[$key] = $ignore;
      }

      $callBack = array('preg','match');
    }
    
    $content = array();
    while (FALSE !== ($filename = readdir($handle))) {
      if ($filename != '.' && $filename != '..' && ! (isset($callBack) && count($ignores) > 0 && array_sum(array_map($callBack,array_fill(0,count($ignores),$filename),$ignores)) > 0)) {  // wenn keine ignore regel matched
          
        if (is_file($this->to_string().$filename)) {
          $file = new File($this,$filename);

          if (isset($extensions) && (is_string($extensions) && $file->getExtension() != $extensions || is_array($extensions) && !in_array($file->getExtension(), $extensions)))
            continue;

          $content[] = $file;
        }
          
        if (is_dir($this->to_string().$filename) && !isset($extensions)) { // wenn extensions gesetzt ist, keine verzeichnisse
          $directory = new Dir($this->to_string().$filename.$this->getDS());
          $directory->ignores = array_merge($directory->ignores,$ignores); // wir vererben unsere ignores

          $content[] = $directory;
        }
      }
    }
    closedir($handle);

    if ($sort !== NULL) {
        
      if ($sort & self::ORDER_ASC) {
        $order = 'asc';
      } elseif ($sort & self::ORDER_DESC) {
        $order = 'desc';
      } else {
        $order = 'asc';
      }

      /* alphabetisch sortieren */
      if ($sort & self::SORT_ALPHABETICAL) {
          
        if ($order == 'asc') {
          $function = create_function('$a,$b',
                                      'return strcasecmp($a->getName(),$b->getName()); ');
        } else {
          $function = create_function('$a,$b',
                                      'return strcasecmp($b->getName(),$a->getName()); ');
        }

        uasort($content, $function);
      }
    }

    return $content;
  }


  /**
   * Gibt alle Dateien (auch in Unterverzeichnissen) zurück
   * 
   * für andere Parameter siehe getContents()
   * @param bool $subdirs wenn TRUE wird auch in Subverzeichnissen gesucht
   * @see getContents()
   */
  public function getFiles($extensions = NULL, Array $ignores = NULL, $subdirs = TRUE) {
    /* wir starten eine Breitensuche (BFS) auf dem Verzeichnis */
    
    $files = array();
    $dirs = array(); // Verzeichnisse die schon besucht wurden
    $queue = array($this);

    while (count($queue) > 0) {
      $elem = array_pop($queue);

      /* dies machen wir deshalb, da wenn extension gesetzt ist, keine verzeichnisse gesetzt werden */
      foreach($elem->getContents(NULL,$ignores) as $item) {
        if ($item instanceof Dir && !in_array((string) $item, $dirs)) { // ist das verzeichnis schon besucht worden?

          if ($subdirs) // wenn nicht wird hier nichts der queue hinzugefügt und wir bearbeiten kein unterverzeichnis
            array_unshift($queue,$item);

          /* besucht markieren */
          $dirs[] = (string) $item;
        }
      }
      
      foreach($elem->getContents($extensions,$ignores) as $item) {
        if ($item instanceof File) {
          $files[] = $item;
        }
      }
    }
    
    return $files;
  }

  /**
   * Setzt die Zugriffsrechte des Verzeichnisses
   * 
   * Z.b. $file->chmod(0644);  für // u+rw g+rw a+r
   * @param octal $mode 
   * @param int $flags
   * @chainable
   */
  public function chmod($mode, $flags = NULL) {
    $ret = chmod((string) $this,$mode);
    
    if ($ret === FALSE)
      throw new Exception('chmod für '.$this.' auf '.$mode.' nicht möglich');
      

    if ($flags & self::RECURSIVE) {
      foreach ($this->getContents() as $item) {
        if (is_object($item) && ($item instanceof File || $item instanceof Dir)) {
          $item->chmod($mode, $flags);
        }
      }
    }
    
    return $this;
  }

  /**
   * Löscht das Verzeichnis rekursiv
   * 
   * @chainable
   */
  public function delete() {
    if ($this->exists()) {
      
      foreach ($this->getContents() as $item) {
        if (is_object($item) && ($item instanceof File || $item instanceof Dir)) {
          $item->delete(); // rekursiver aufruf für Dir
        }
      }
      
      @rmdir((string) $this); // selbst löschen
    }

    return $this;
  }



  /**
   * Kopiert das Verzeichnis
   * 
   * Kopiert ein Verzeichnis mit allen Dateien und Unterverzeichnissen in ein neues Verzeichnis
   * @param Dir $destination
   * @chainable
   */
  public function copy(Dir $destination) {
    if ((string) $destination == (string) $this)
      throw new Exception('Kann nicht kopieren: Zielverzeichnis und Quellverzeichns sind gleich.');

    if (!$destination->exists())
      $destination->make();

    foreach ($this->getContents() as $item) {
      if ($item instanceof File) {
        $destFile = clone $item;
        $destFile->setDirectory($destination);
        $item->copy($destFile); 
      }
        
      if ($item instanceof Dir) {
        $relativeDir = clone $item; 
        $relativeDir->makeRelativeTo($this); // sowas wie ./unterverzeichnis
        
        $destDir = clone $destination;
        $destDir->append($relativeDir); // path/to/destination/unterverzeichnis
        $item->copy($destDir); //rekursiver Aufruf
      }
    }
    return $this;
  }

  /**
   * Verschiebt das Verzeichnis
   * 
   * @param Dir $destination
   * @chainable
   */
  public function move(Dir $destination) {
    $ret = @rename((string) $this,(string) $destination);

    $errInfo = 'Kann Verzeichnis '.$this.' nicht nach '.$destination.' verschieben / umbenennen.';
    
    if (!$ret) {
      if ($destination->exists())
        throw new Exception($errInfo.' Das Zielverzeichnis existiert.');

      if (!$this->exists())
        throw new Exception($errInfo.' Das Quellverzeichnis existiert nicht.');
      else 
        throw new Exception($errInfo);
    }


    /* wir übernehmen die Pfade von $destination */
    $this->path = $destination->getPathArray();
    return $this;
  }


  /**
   * Erstellt das Verzeichnis physikalisch
   * 
   * -p erstellt die Verzeichnisse für den Pfad solange diese noch nicht existieren
   * @param $options der OptionString 
   * @chainable
   */
  public function make($options=NULL) {
    /* optionen */
    $parent = (mb_strpos($options,'-p') !== FALSE);

    /* erstellt alle Parent-Verzeichnise im Pfad die noch nicht existieren (nicht das Verzeichnis selbst) */
    if ($parent) {
      $ar = $this->getPathArray();
      if (count($ar) >= 2) {
        array_pop($ar); // verzeichnis selbst entfernen

        $dir = clone $this;
        $dir->setPath(NULL);
        foreach ($ar as $part) {
          $dir->append($part);

          if (!$dir->exists()) {
            $dir->make(); // ohne -p!
          }
        }
      }
    }
      
    if (!$this->exists()) {
      /* erstelle das verzeichnis */
      $ret = @mkdir((string) $this);
      if ($ret == FALSE) {
        throw new Exception('Fehler beim erstellen des Verzeichnisses: '.$this);
      }
    } else {
      throw new Exception('Verzeichnis '.$this.' kann nicht erstellt werden, da es schon existiert');
    }

    return $this;
  }

  /**
   * Überprüft ob eine bestimmte Datei im Verzeichnis liegt (und gibt diese zurück)
   * 
   * Wird ein File Objekt übergebeben wird der Name der Datei überprüft.
   * Gibt FALSE zurück, wenn die Datei nicht existiert. Ein File Objekt, wenn sie existiert.
   * @param string|File $file
   * @return bool|File
   */
  public function getFile($file) {
    if (is_object($file) && $file instanceof File)
      $file = $file->getName();

    $file = new File($file,$this);
    if ($file->exists()) 
      return $file;
    else
      return FALSE;
  }

  /**
   * 
   * @return bool
   */
  public function exists() {
    if (count($this->path) == 0) return FALSE;
    return is_dir((string) $this);
  }

  /**
   * @return bool
   */
  public function isWriteable() {
    if (count($this->path) == 0) return FALSE;
    return is_writable((string) $this);
  }

  /**
   * @return bool
   */
  public function isReadable() {
    if (count($this->path) == 0) return FALSE;
    return is_readable((string) $this);
  }


  /**
   * Gibt den Pfad als String zurück
   * 
   * je nach Betriebssystem wird ein UNIX oder Windows Pfad zurückgegeben. <br />
   * jedes Verzeichnis ohne Trailingslash zurückgegeben.
   * @return string
   */
  public function getPath() {
    $pathString = implode($this->path, $this->getDS());

    return $pathString.$this->getDS();
  }

  /**
   * 
   * @return array
   */
  public function getPathArray() {
    return $this->path;
  }

  public function getName() {
    if (count($this->path) > 0)
      return $this->path[count($this->path)-1];
  }

  /**
   * 
   */
  public function getDS() {
    return '/';
  }

  /**
   * 
   * @return string WINDOWS|UNIX
   */
  public function getOS() {
    if (mb_substr(PHP_OS, 0, 3) == 'WIN') {
      $os = 'WINDOWS';
    } else {
      $os = 'UNIX';
    }
    return $os;
  }


  public function to_string() {
    return $this->getPath();
  }

  public function __toString() {
    return $this->to_string();
  }


  /**
   * Entfernt (sofern vorhanden) den Trailingslash aus einer Pfadangabe
   * @return string die Pfadangabe ohne den Trailingslash
   */
  public static function unTrailSlash($path) {
    $unSlPath = $path;
    if (string::ends_with($path,'/') || string::ends_with($path,'\\')) {
      $unSlPath = mb_substr($path,0,-1);
    }
    return $unSlPath;
  }


  /**
   * Extrahiert das Verzeichnis aus einer Angabe zu einer Datei
   * 
   * @param string $string der zu untersuchende string
   * @return Dir
   */
  public static function extract($string) {
    if (mb_strlen($string) == 0) {
      throw new Exception('String ist leer, kann kein Verzeichnis extrahieren');
    }
    
    $path = dirname($string);
    try {
      $dir = new Dir($path);
    } catch (Exception $e) {
      throw new Exception('kann kein Verzeichnis aus dem extrahierten Verzeichnis "'.$path.'" erstellen: '.$e->getMessage());
    }
    
    return $dir;
  }

  /**
   * Gibt eine neue Instanz zurück
   * @param string $path
   * @return Dir
   */
  public static function factory($path = NULL) {
    return new Dir($path);
  }
}

?>