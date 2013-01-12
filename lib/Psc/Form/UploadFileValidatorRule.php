<?php

namespace Psc\Form;

class UploadFileValidatorRule implements FieldValidatorRule {
  
  protected $field;
  protected $publicFileName = NULL;
  
  public function __construct($field = NULL) {
    $this->field = $field;
  }
  
  public function validate($data) {
    // die $data hier ist relativ uninteressant
    // wir machen hier ein bißchen dirty-action
    if (!isset($_FILES)) throw new EmptyDataException();
    
    $field = $this->field;
    
    if (!array_key_exists($field, $_FILES)) {
      throw new EmptyDataException();
    }
    $fileArray = $_FILES[$field];
    
    if ($fileArray['size'] <= 0) {
      throw new \Psc\Exception('Die größe der hochgeladenen Datei war 0.');
    }
      
    $this->publicFilename = $fileArray['name'];
    
    $file = new \Webforge\Common\System\File($fileArray['tmp_name']);
    if ($file->exists()) {
      return $file;
    }
    
    throw new \Psc\Exception('Konnte nicht hochgeladen werden. Temporäre Datei nicht gefunden.');
  }

  public function setField($field) {
    $this->field = $field;
  }
}

?>