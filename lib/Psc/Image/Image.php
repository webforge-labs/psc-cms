<?php

namespace Psc\Image;

interface Image {
  
  public function getImageManager();

  public function setImageManager(\Psc\Image\Manager $manager);
    
  public function getImagineImage();
  
  public function getUrl();
  
  /**
   * $options['format'] = 'png|jpg';
   * $options['quality'] = 0..100;
   */
  public function getThumbnail($width, $height, $method = 'standard', Array $options = array());
  
  public function getThumbnailURL($format = 'default');
  
  // setImagineImage
  
  //public function setSourceFile(File $file) // absolute
  //public function setSourcePath($path) // relative
  
  // setLabel
  
  // setImagineImage
  
  public function export();
  
}
