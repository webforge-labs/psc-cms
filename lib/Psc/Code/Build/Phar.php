<?php

namespace Psc\Code\Build;

use Webforge\Common\System\Dir,
    Webforge\Common\System\File,
    Psc\Code\Code,
    Psc\PSC,
    \Phar AS PHPPhar
  ;

/**
 * Erstellt ein Phar-Binary-File für einen Namespace / eine Library
 *
 * Die Bootstrap kann injected werden,
 * die ist aber auch relativ langweilig, dann die lädt nur den auto-loader
 */
class Phar extends \Psc\Object {
  
  public $log;
  
  /**
   * @var Webforge\Common\System\File
   */
  protected $out;
  
  /**
   * @var Webforge\Common\System\Dir
   */
  protected $classPath;
  
  /**
   * @var string
   */
  protected $namespace;
  
  protected $logger;
  protected $logLevel = 1;
  
  /**
   * Der Gesamte Code der ausgeführt werden soll, wenn das PHAR geladen wird
   *
   *
   * /*%%CLASS2PATH%%*  / wird mit einer Array-Expression ersetzt die eine liste für einen Autoloader aller im phar integrierten Dateien von $class => $file hat
   * $file ist dabei der relative Pfad der Datei im Pfad
   * der \Psc\PharAutoloader benutzt solch ein Konstrukt
   * @var string
   */
  protected $bootstrapCode;
  
  /**
   *
   * @var array spec siehe addFile()
   */
  protected $additionalFiles = array();
  
  /**
   * @var array<Webforge\Common\System\File>
   */
  protected $additionalClassFiles = array();
  
  /**
   * @var Webforge\Common\System\Dir
   */
  protected $tempSrc;
  
  /**
   * @var \Phar
   */
  protected $phar;
  
  
  /**
   * wenn TRUE wird dies an mapFileToClass weitergegeben
   */
  protected $underscoreStyle = FALSE;
  
  /**
   *
   * @param File $out die Datei in die das phar geschrieben werden soll
   * @param Dir $classPath das Verzeichnis für die Dateien von $namespace z. B. Umsetzung\base\src\SerienLoader\
   * @param string $namespace der Name des Namespaces dessen Klassen in $classPath liegen z. B. SerienLoader
   */
  public function __construct(File $out, Dir $classPath, $namespace, $logger = NULL) {
    $this->out = $out;
    $this->namespace = $namespace;
    $this->classPath = $classPath;
    $this->logger = $logger;
    $this->logLevel = 2;
    
    if (\Webforge\Common\String::endsWith($this->namespace,'_')) {
      $this->underscoreStyle = TRUE;
    }
  }
  
  public function build() {
    $this->prepareTemp();
    
    /* Dateien ins Temp-Verzeichnis kopieren */
    $class2path = array();
    
    $relDir = $this->classPath->up(); // wir wollen ja den Namespace als erstes Verzeichnis haben
    foreach ($this->getClassFiles() as $file) {
      $class = $this->inferClassName($file);
      
      // $class2path[ $class ] = $fileURL;
      $class2path[ ltrim($class,'\\') ] = $url = $file->getURL($relDir);
      
      // statt hier Code::mapClassToFile() zu machen nehmen wir die url
      $targetFile = File::createFromURL($url, $this->tempSrc);
      $targetFile->getDirectory()->create();
      
      $this->log(str_pad('Add: '.$url,80,' ',STR_PAD_RIGHT)." [".$class."]",2);
      
      $file->copy($targetFile);
    }
    
    foreach ($this->additionalFiles as $list) {
      list($file,$fileInPhar) = $list;
      $targetFile = new File($this->tempSrc.$fileInPhar);
      $targetFile->getDirectory()->create();
      $this->log('Add: '.$fileInPhar);
      $file->copy($targetFile);
    }
    
    /* Bootstrapping */
    $bootstrapCode = $this->getBootstrapCode($class2path);
    $bootstrapFile = new File($this->tempSrc, 'index.php');
    $bootstrapFile->writeContents($bootstrapCode);

    /* Build */
    try {
      $tmp = File::createTemporary();
      $tmp->setExtension('phar.gz');
      
      $this->phar = new PHPPhar((string) $tmp);
      $this->phar->compress(PHPPHAR::GZ);
      $this->phar->startBuffering();
      $this->phar->buildFromDirectory($this->tempSrc);
      $this->phar->stopBuffering();
      
      $this->tempSrc->delete();
      $this->out->delete();
      $tmp->copy($this->out);
      
    } catch (\Exception $e) {
      $this->tempSrc->delete();
      
      throw $e;
    }
  }
  
  /**
   * Fügt eine beliebige Datei zum Phar hinzu
   *
   * Achtung: wenn dies eine PHP-Datei ist mit einer Klasse drin, wird diese nicht "indiziert", sondern einfach nur dump eingefügt
   * Dies ist also nur für andere PHARs oder andere Dateien ohne PHP-Klassen darin
   * @param File $fileInPhar ist eine RELATIVE Datei => d. h. das Verzeichnis ist relativ und bestimmt den Namen und Ort der Datei im Phar
   */
  public function addFile(File $file, File $fileInPhar) {
    if (!$fileInPhar->getDirectory()->isRelative()) {
      throw new \Psc\Exception('fileInPhar muss ein relatives Verzeichnis haben');
    }
    
    $this->additionalFiles[(string) $fileInPhar] = array($file, $fileInPhar);
    return $this;
  }
  
  public function addClassFile(File $file) {
    $this->additionalClassFiles[] = $file;
    return $this;
  }
  
  public function getClassFiles() {
    return array_merge(
      $this->classPath->getFiles('php',NULL, TRUE),
      $this->getAdditionalClassFiles()
    );
  }
  
  public function getAdditionalFiles() {
    return $this->additionalFiles;
  }
  
  protected function getBootstrapCode(Array $class2path) {
    if (!isset($this->bootstrapCode)) {
      $this->bootStrapCode = '<?php /* empty bootstrap */ ?>';
    }

    /* replace in bootstrap */
    return str_replace(
      array(
        '/*%%CLASS2PATH%%*/',
      ),
      array(
        var_export($class2path,true),
      ),
      $this->bootstrapCode
    );
  }

  /**
   * Gibt zu einer Datei im Phar die Klasse zurück
   *
   * dies funktioniert im Moment nur mit der Konvention, dass jedes Verzeichnis ein unter-namespace von $this->namespace ist
   */
  protected function inferClassName(File $file) {
    return Code::mapFileToClass($file, $this->classPath->up(), $this->underscoreStyle ? '_' : '\\');
  }
  
  protected function prepareTemp() {
    $this->tempSrc = PSC::get(PSC::PATH_FILES)->sub('tmp/'.uniqid('build-phar'));
    
    /* temp verzeichnis leer anlegen */
    if (!$this->tempSrc->exists())
      $this->tempSrc->make(Dir::PARENT);
    else
      $this->tempSrc->wipe();
    
    /* out datei löschen (macht nichts, wenn nicht existiert) */
    $this->out->delete();
  }
  
  protected function cleanUp() {
    $this->tempSrc->wipe()->delete();
  }
  
  public function unwrap() {
    return $this->phar;
  }
  
  public function log($msg, $level = 1) {
    if ($level <= $this->logLevel) {
      if (isset($this->logger))
        $this->logger->writeln('[Phar] '.$msg);
      
      $this->log[] = $msg;
    }
  }
  
  public function debug() {
    print \Psc\A::join($this->log,"    [phar] %s\n");
  }
}
?>