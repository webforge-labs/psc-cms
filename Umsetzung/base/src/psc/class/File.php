<?php

class File extends PolymorphObject {

  const FORCE_DETECTION = 10; // ergebnisse werden hart neu berechnet (siehe md5hash() oder getMimeType())
  const EXCLUSIVE = 11; // für write Contents

  const WITHOUT_EXTENSION = FALSE;
  const WITH_EXTENSION = TRUE;

  /**
   * Der Name der Datei OHNE Extension
   * @var string 
   */
  protected $name;

  /**
   * Die Dateiendung ohne .
   * @var string
   */
  protected $extension;

  /**
   * Das Verzeichnis in dem die Datei liegt
   * @var Dir
   */
  protected $directory;

  /**
   * Der Mimetype der Datei
   * @var string
   */
  protected $mimeType;

  /**
   * Der gecachte MD5 Hash
   * @var string
   */
  protected $hashMD5;


  /**
   * 
   * @var array
   */
  protected $polymorphConstructors = array(
    'string'=>array('string'),
    'default2'=>array('object:Dir','string'),
    'default'=>array('string','object:Dir'),
  );

  /**
   * Erstelle eine neue Datei aus einem Pfad als String
   * @param string $file
   */
  protected function constructString($file) {
    if (mb_strlen($file) == 0)
      throw new Exception('keine Datei angegeben');
    
    $dir = NULL;
    try {
      $dir = Dir::extract($file);
    } catch (Exception $e) {
      /* kein Verzeichnis vorhanden, vll ein Notice? (aber eigentlich sollte dies ja auch okay sein */
    }
    
    try {
      $filename = self::extractFilename($file);
    } catch (Exception $e) {
      throw new Exception ('Fehler beim Erstellen einer Datei aus einem Pfad: '.$e->getMessage());
    }

    $this->constructDefault($filename,$dir);
  }

  /**
   * Erstellt eine Datei aus einem Verzeichnis und einem Dateinamen
   * 
   * @param string $filename der Name der Datei
   * @param Dir $directory das Verzeichnis in dem die Datei liegt
   */
  protected function constructDefault($filename, Dir $directory=NULL) {
    $this->setName($filename);
    
    if (isset($directory)) 
      $this->setDirectory($directory);
  }
  /**
   * Erstellt eine Datei aus einem Verzeichnis und einem Dateinamen
   * 
   * @param dir $directory
   * @param string $filename
   */
  protected function constructDefault2(Dir $directory, $filename) {
    $this->constructDefault($filename,$directory);
  }



  /**
   * Schreibt den Inhalt des Strings in die Datei
   * 
   * Is File::EXCLUSIVE gesetzt, wird zuerst ein eine temporäre Datei geschrieben und dann diese Datei auf die Zieldatei ($this) gemoved.<br />
   * Dies ist dann wichtig, wenn man mehrere Requests hat, die auf die selbe Datei schreiben wollen
   * @param string $contents
   * @param int $flags kann File::EXCLUSIVE sein
   */
  public function writeContents($contents, $flags = NULL) {
    if ($this->exists() && !$this->isWriteable()) {
      throw new Exception('Dateiinhalt kann nicht geschrieben werden. (exists/writeable) '.$this);
    }

    if ($flags & self::EXCLUSIVE) {
      
      $tmpFile = new File(tempnam('/tmp', 'filephp'));
      $tmpFile->writeContents($contents);

      $ret = $tmpFile->move($this,TRUE); // 2ter Parameter ist overwrite
      
      if ($ret === FALSE)
        throw new Exception('Dateiinhalt konnte nicht geschrieben werden move() gab FALSE zurück');

    } else {
      $ret = file_put_contents((string) $this,$contents);

      if ($ret === FALSE)
        throw new Exception('Dateiinhalt konnte nicht geschrieben werden PHP::file_put_contents gab FALSE zurück');
    }
  }

  /**
   * Gibt den Inhalt der Datei als String zurück
   * 
   * @param int $maxlength in bytes
   * @return string
   * @exception wenn die Datei nicht existiert oder nicht lesbar ist
   */
  public function getContents($maxLength = NULL) {
    if (!$this->exists() || !$this->isReadable())
      throw new Exception('Datei nicht lesbar: '.$this);

    if (isset($maxLength)) {
      $ret = file_get_contents((string) $this,
                               NULL, // flags
                               NULL, // context
                               NULL, // offset
                               $maxLength
        );
    } else {
      $ret = file_get_contents((string) $this); /* get all */
    }

    if ($ret === FALSE)
      throw new Exception('dateiinhalt konnte nicht ausgelesen werden PHP::file_get_contents gab FALSE zurück');

    return $ret;
  }

  /**
   * Verschiebt die Datei zur angegebenen Datei
   * 
   * Anders als copy, werden die Informationen aus dem $fileDestination Objekt übernommen, sodass:<br />
   * <code>(string) $fileDestination-> === (string) $this</code> ist. 
   * @param File $fileDestination
   */
  public function move(File $fileDestination, $overwrite = FALSE) {
    if (!$this->exists())
      throw new Exception('Quelle von move existiert nicht');

    if ($fileDestination->exists() && !$overwrite) {
      throw new Exception('Das Ziel von move existiert bereits');
    }

    if (!$fileDestination->getDirectory()->exists()) {
      throw new Exception('Das ZielVerzeichnis von move existiert nicht: '.$fileDestination->getDirectory());
    }
   
    if ($fileDestination->exists() && $overwrite && mb_substr(PHP_OS, 0, 3) == 'WIN') {
      $fileDestination->delete();
    }
    $ret = rename((string) $this, (string) $fileDestination);
    
    /* wir übernehmen die Informationen von File Destination */
    if ($ret) {
      $this->setDirectory($fileDestination->getDirectory());
      $this->setName($fileDestination->getName());
    }

    return $ret;
  }


  /**
   * Kopiert die Datei auf die angegebene Datei
   * @param File $fileDestination
   * @chainable
   */
  public function copy(File $fileDestination) {
    
    if (!$this->exists())
      throw new Exception('Quelle von copy existiert nicht');

    if (!$fileDestination->getDirectory()->exists()) {
      throw new Exception('Das Verzeichnis von copy existiert nicht: '.$fileDestination->getDirectory());
    }

    $ret = copy((string) $this,(string) $fileDestination);

    if (!$ret) {
      throw new Exception('Fehler beim Kopieren von '.$this.' zu '.$fileDestination);
    }
    return $this;
  }


  /**
   * Löscht die Datei
   * 
   * Gibt True oder False bei Erfolg/Misserfolg zurück
   * @return bool
   */
  public function delete() {
    if (!$this->exists()) return TRUE;
    if (!$this->isWriteable()) return FALSE;

    @unlink((string) $this);
    
    return $this->exists();
  }

  /**
   * Setzt die Zugriffsrechte der Datei
   * 
   * Z.b. $file->chmod(0644);  für // u+rw g+rw a+r
   * @param octal $mode 
   * @chainable
   */
  public function chmod($mode) {
    $ret = chmod((string) $this,$mode);
    
    if ($ret === FALSE)
      throw new Exception('chmod für '.$this.' auf '.$mode.' nicht möglich');

    return $this;
  }

  /**
   * Überprüft ob die Datei im Dateisystem exisitert
   * @return bool
   */
  public function exists() {
    if (mb_strlen($this->getName()) == 0) return FALSE;
    return file_exists((string) $this);
  }

  /**
   * Überprüft ob eine Datei lesbar ist
   * @return bool
   */
  public function isReadable() {
    if ($this->exists()) {
      return is_readable((string) $this);
    } else {
      return FALSE;
    }
  }

  /**
   * Überprüft ob eine Datei schreibbar ist
   * @return bool
   */
  public function isWriteable() {
    if ($this->exists()) {
      return is_writable((string) $this);
    } else {
      return FALSE;
    }
  }


  /**
   * Setzt den Dateinamen der Datei
   * 
   * @var string Dateiname inklusive Extension
   * @chainable
   */
  public function setName($name) {

    if (($pos = mb_strrpos($name,'.')) !== FALSE) {
      $this->name = mb_substr($name,0,$pos); /* Dateinamen ohne Erweiterung */
      $this->extension = mb_substr($name,$pos+1); /* Erweiterung */
    } else {
      $this->name = $name;
    }
    
    return $this;
  }

  /**
   * Gibt den Dateinamen der Datei zurück (inklusive Extension)
   * @return string
   */
  public function getName($extension = self::WITH_EXTENSION) {
    if (isset($this->extension) && $extension == self::WITH_EXTENSION)
      return $this->name.'.'.$this->extension;
    else
      return $this->name;
  }


  /**
   * 
   * @param Dir $directory
   */
  public function setDirectory(Dir $directory) {
    $this->directory = $directory;
  }

  /**
   * 
   * @return Dir
   */
  public function getDirectory() {
    return $this->directory;
  }

  public function __toString() {
    $dir = (string) $this->getDirectory();
    
    return $dir.$this->getName();
  }

  /**
   * Ersetzt (wenn vorhanden) die Extension des angegebenen Dateinamens mit einer Neuen
   * 
   * Ist keine Extension vorhanden, wird die zu ersetzende angehängt
   * ist <var>$extension</var> === NULL wird die Extension gelöscht
   * 
   * @param string $extension die Erweiterung (der . davor ist optional)
   * @chainable
   */
  public function setExtension($extension=NULL) {
    if ($extension != NULL && mb_strpos($extension,'.') === 0) 
      $this->extension = mb_substr($extension,1);
    else 
      $this->extension = $extension;

    return $this;
  }

  /**
   * Extrahiert eine Datei aus einer Pfadangabe
   * 
   * Der Pfad wird nicht in die Informationen in File mit aufgenommen
   * @param string $string der Pfad zur Datei
   * @return File die extrahierte Datei aus dem Pfad
   */
  public function extract($string) {
    return new File(self::extractFilename($string));
  }


  /**
   * Extrahiert eine Datei als String aus einer Pfadangabe
   * 
   * @param string $string ein Pfad zu einer Datei
   * @return string name der Datei
   */
  public static function extractFilename($string) {
    if (mb_strlen($string) == 0) 
      throw new Exception('String ist leer');
    
    $file = basename($string);
    if (mb_strlen($file) == 0) 
      throw new Exception('es konnte kein Dateiname extrahiert werden ');
    
    return $file;
  }
}

?>