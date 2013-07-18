<?php

namespace Psc\System;

/**
 * 
 */
class UploadedFile extends File {
  
  /**
   * @var string
   */
  protected $originalName;
  
  /**
   * @param string $originalName
   */
  public function setOriginalName($originalName) {
    $this->originalName = $originalName;
    return $this;
  }
  
  /**
   * @return string
   */
  public function getOriginalName() {
    return $this->originalName;
  }
}
