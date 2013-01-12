<?php

namespace Psc\System;

abstract class Archive extends \Psc\Object {
  
  const FILES = 0x000001;
  const DIRECTORIES = 0x000002;
  
  const FILES_OR_DIRECTORIES = 0x000003;
  
  protected $file;
  
  public function __construct(File $file) {
    $this->file = $file;
    $this->setUp();
  }
  
  protected function setUp() {
  }
  
  abstract public function getContents($filter = self::FILES_OR_DIRECTORIES);
  
  public function getFiles($extension = NULL) {
    $contents = $this->getContents(self::FILES);
    
    if ($extension !== NULL) {
      $contents = array_filter($contents, function ($archiveFile) use ($extension) {
        return $arcthiveFile->getExtension() === $extension;
      });
    }
    
    return $contents;
  }
  
  public function getDirectories() {
    return $this->getContents(self::DIRECTORIES);
  }
}
?>