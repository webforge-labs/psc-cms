<?php

namespace Psc\Doctrine\Entities;

use Webforge\Common\System\File;
use Webforge\Common\System\Dir;
use Psc\CMS\UploadManager;
use Psc\Code\Code;
use Doctrine\ORM\Mapping AS ORM;
use Psc\CMS\UploadedFile;
use Psc\TPL\ContentStream\Context;
use Psc\TPL\ContentStream\ContextLoadable;

/**
 * 
 * Benutze den CommonProjectCompiler, um eine ableitendes Entity zu erstellen compileFile()
 */
abstract class BasicUploadedFile extends \Psc\CMS\AbstractEntity implements \Psc\CMS\UploadedFile, ContextLoadable {
  
  /**
   * @var Webforge\Common\System\File
   */
  protected $file;
  
  /**
   * @var Psc\CMS\UploadManager
   */
  protected $manager;
  
  /**
   * @var string
   */
  protected $downloadFilename;

  /**
   * @inheritdoc
   * @return File|NULL
   */
  public static function loadWithContentStreamContext($value, Context $context) {
    return $context->getUploadManager()->load($value);
  }
  
  public function getURL($filename = NULL) {
    return $this->getManager()->getURL($this, $filename);
  }
  
  public function getFile() {
    if (!isset($this->file)) {
      $this->file = $this->getManager()->getSourceFile($this);
    }
    
    return $this->file;
  }
  
  /**
   * @param Webforge\Common\System\File $file
   */
  public function setFile(File $file) {
    $this->file = $file;
    return $this;
  }
  
  /**
   * @return Psc\CMS\UploadManager
   */
  public function getManager() {
    if (!isset($this->manager)) {
      throw new \Psc\Exception('UploadedFile ist mit keinem Manager verbunden. '.$this);
    }
    return $this->manager;
  }
  
  public function setManager(UploadManager $manager) {
    $this->manager = $manager;
    return $this;
  }
  
  /**
   * Exportiert das Entity für z.B. die Controller Response
   */
  public function export() {
    $export = parent::export();
    $export->url = $this->getURL(); // url für die response hinzufügen
    $export->size = $this->getFile()->getSize();
    unset($export->file);
    
    return $export;
  }
  
  /**
   * LifecycleCallback wird beim löschen aufgerufen
   * 
   * @TODO geht noch nicht jetzt
   */
  public function triggerRemoved() {
    return $this->getManager()->listenRemoved($this);
  }
  
  /**
   * @param string $downloadFilename
   */
  public function setDownloadFilename($downloadFilename) {
    $this->downloadFilename = $downloadFilename;
    return $this;
  }
  
  /**
   * @return string
   */
  public function getDownloadFilename() {
    if (!isset($this->downloadFilename) && $this->getOriginalName() != NULL) {
      return $this->getOriginalName();
    }
    
    return $this->downloadFilename;
  }
  
  public function getDisplayExtension() {
    if ($this->getOriginalName() != NULL) {
      return File::extractExtension($this->getOriginalName());
    }
    
    return $this->getFile()->getExtension();
  }
}
?>