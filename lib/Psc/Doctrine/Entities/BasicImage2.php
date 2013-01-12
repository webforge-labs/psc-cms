<?php

namespace Psc\Doctrine\Entities;

use Webforge\Common\System\File;
use Webforge\Common\System\Dir;
use Psc\Image\Manager;
use Psc\Code\Code;
use Imagine\Image\ImageInterface as ImagineImage;
use Doctrine\ORM\Mapping AS ORM;

/**
 *
 *
 * Benutze den CommonProjectCompiler, um eine ableitendes Entity zu erstellen compileImage()
 * 
 */
abstract class BasicImage2 extends \Psc\CMS\AbstractEntity implements \Psc\Image\Image {
  
  /**
   * @var Webforge\Common\System\File
   */
  protected $sourceFile;

  /**
   * Der Pfad zur Original-Resource der Datei (relativ zum files/images/ ordner)
   *
   * @var string
   */
  protected $sourcePath;
  
  /**
   * Ein Hash der den Inhalt des Bildes hashed
   *
   * dieser wird gebraucht umd das Bild zu vergleichen
   * @var string
   */
  protected $hash;
  
  /**
   * Eine optionale Bezeichnung für das Bild
   * 
   * @var string
   */
  protected $label;
  
  /**
   * @var Psc\Image\Manager
   */
  protected $imageManager;
  
  /**
   * @var Imagine\Image\ImageInterface
   */
  protected $imagineImage;

  /**
   * Die voreingestellten Formate für Thubmnails
   *
   * @var array string $identifier=>list($width, $height, outbound|standard $method)
   */
  protected $formats = array(
    //'page-default'=>array(226, 136, 'outbound'),
    //'search'=>array(166, 124, 'standard'),
    //'detail-thumb'=>array(104, 73, 'standard'),
    //'detail-featured'=>array(296, 271, 'standard')
  );

  public function getThumbnailURL($format = 'page-default') {
    if (array_key_exists($format,$this->formats)) {
      return $this->getURL('thumbnail', $this->formats[$format]);
    }
    
    throw new \Psc\Exception('Unbekanntes Format: '.Code::varInfo($format));
  }
  
  /**
   * @return ImagineImage
   */
  public function getThumbnail($width, $height, $method = 'standard') {
    return $this->getImageManager()->getVersion($this,'thumbnail',array($width,$height,$method));
  }
  
  public function getURL($type = 'original', Array $arguments = array()) {
    return $this->getImageManager()->getURL($this,$type,$arguments);
  }
  
  public function getSourceFile() {
    if (!isset($this->sourceFile)) {
      $this->sourceFile = $this->getImageManager()->getSourceFile($this);
    }
    
    return $this->sourceFile;
  }
  
  public function setSourceFile(File $file) {
    $this->sourceFile = $file;
    return $this;
  }
  
  
  public function export() {
    $export = parent::export();
    $export->url = $this->getURL();
    
    return $export;
  }
  
  
  /**
   * @return Psc\Image\Manager
   */
  public function getImageManager() {
    if (!isset($this->imageManager)) {
      throw new \Psc\Exception('Image ist mit keinem Manager verbunden. '.$this);
    }
    return $this->imageManager;
  }

  public function setImageManager(\Psc\Image\Manager $manager) {
    $this->imageManager = $manager;
    return $this;
  }
  
  /**
   * @return ImagineImage
   */
  public function getImagineImage() {
    if (!isset($this->imagineImage))  {
      $this->imagineImage = $this->getImageManager()->getImagineImage($this);
    }
    
    return $this->imagineImage;
  }
  
  /**
   * @param Imagine\Image\ImageInterface $im
   */
  public function setImagineImage(ImagineImage $im) {
    $this->imagineImage = $im;
    return $this;
  }
  
  /**
   * @return string relativ zum Datenverzeichnis des Managers (nicht full path!)
   */
  public function getSourcePath() {
    return str_replace(array('\\','/'),DIRECTORY_SEPARATOR, $this->sourcePath);
  }
  
  /**
   * LifecycleCallback wird beim löschen aufgerufen
   */
  public function triggerRemoved() {
    return $this->getImageManager()->listenRemoved($this);
  }
}
?>