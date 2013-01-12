<?php

namespace Psc\Code\Build;

use Webforge\Common\System\File;
use Psc\System\Logger;
use Psc\Code\Code;
use Psc\Data\Storage;
use Psc\Data\PHPStorageDriver;
use Psc\Code\Build\Phar;
use Psc\CMS\Project;

class LibraryBuilder extends \Psc\Object {
  
  protected $project;
  
  protected $logger;
  
  protected $storage;
  protected $storageFile;
  
  /**
   * @var Psc\Code\Build\Phar
   */
  protected $phar;
  
  public function __construct(Project $project, Logger $logger = NULL) {
    $this->project = $project;

    $this->storageFile = new File($this->project->getCache(), 'storage.changedFiles.php');
    $this->storage = new Storage(new PHPStorageDriver($this->storageFile));
    $this->logger = $logger ?: new \Psc\System\BufferLogger();
  }
  
  public function compile(File $out) {
    $this->phar = new Phar($out, $this->project->getClassPath(), $this->project->getNamespace(), $this->logger);
    $this->phar->setLogLevel(1);
    
    $this->logger->writeln("  Compiling to ".$out."... ");
    $this->phar->setBootstrapCode(File::factory($this->project->getRoot()->sub('resources/'), 'bootstrap.phar.php')->getContents());
    $this->phar->build();
    
    return $this->phar;
  }
  
  /**
   * @return bool
   */
  public function isCompilingNeeded() {
    if (!$this->storageFile->exists()) {
      $this->logger->writeln('  Fastcheck: Kein Kompilieren noetig.');
      return FALSE;
    }
    
    $changedFiles = $this->storage->init()->getData()->get('files');
    if (count($changedFiles) === 0) return FALSE;
    
    foreach ($changedFiles as $file => $status) {
      $this->logger->writeln('  changed File: '.$file);
    }
    
    return TRUE;
  }
  
  public function resetCompilingNeeded() {
    /* compile-file löschen */
    if (isset($this->storageFile))
      $this->storageFile->delete();
  }
}
?>