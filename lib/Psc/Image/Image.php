<?php

namespace Psc\Image;

interface Image {
  
  public function getImageManager();

  public function setImageManager(\Psc\Image\Manager $manager);
    
  public function getImagineImage();
  
  public function getUrl();
  
  public function getThumbnail($width, $height, $method = 'standard');
  
  public function getThumbnailURL($format = 'default');
  
  // setImagineImage
  
  //public function setSourceFile(File $file) // absolute
  //public function setSourcePath($path) // relative
  
  // setLabel
  
  // setImagineImage
  
  public function export();
  
}

?>
