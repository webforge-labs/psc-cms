<?php

namespace Psc\Doctrine\Entities;

use Webforge\Common\System\File;
use Webforge\Common\System\Dir;
use Psc\Image\Manager;
use Imagine\Image\ImageInterface as ImagineImage;
use Doctrine\ORM\Mapping AS ORM;

abstract class BasicImage extends \Psc\Doctrine\Object implements \Psc\Image\Image {
  
  /**
   * @var Webforge\Common\System\File
   */
  protected $sourceFile;

  /**
   * @ORM\Id
   * @ORM\GeneratedValue
   * @ORM\Column(type="integer")
   * @var int
   */
  protected $id;
  
  /**
   * Der Pfad zur Original-Resource der Datei (relativ zum files/images/ ordner)
   *
   * @ORM\Column(type="string")
   * @var string
   */
  protected $sourcePath;
  
  
  /**
   * Ein Hash der den Inhalt des Bildes hashed
   *
   * dieser wird gebraucht umd das Bild zu vergleichen
   * @ORM\Column(type="string")
   * @var string
   */
  protected $hash;
  
  /**
   * @ORM\Column(type="string",nullable=true)
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
  
  public function getThumbnail($width, $height, $method = 'standard') {
    return $this->getImageManager()->getVersion($this,'thumbnail',array($width,$height,$method));
  }
  
  public function getURL($type = 'original', Array $arguments = array()) {
    return $this->getImageManager()->getURL($this,$type,$arguments);
  }
  
  public function getIdentifier() {
    return $this->id;
  }
  
  public function getSourceFile() {
    if (!isset($this->sourceFile)) {
      $this->sourceFile = $this->getImageManager()->getSourceFile($this);
    }
    
    return $this->sourceFile;
  }
  
  public function getImageManager() {
    if (!isset($this->imageManager)) {
      throw new \Psc\Exception('Image ist mit keinem Manager verbunden. '.$this);
    }
    return $this->imageManager;
  }
  
  public function getImagineImage() {
    if (!isset($this->imagineImage))  {
      $this->imagineImage = $this->getImageManager()->getImagineImage($this);
    }
    
    return $this->imagineImage;
  }
  
  public function getSourcePath() {
    return str_replace(array('\\','/'),DIRECTORY_SEPARATOR, $this->sourcePath);
  }
  
  public function setImageManager(\Psc\Image\Manager $manager) {
    $this->imageManager = $manager;
    return $this;
  }
  
  public function triggerRemoved() {
    return $this->getImageManager()->listenRemoved($this);
  }
}
?>